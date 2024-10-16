<?php

declare(strict_types=1);

/*
 * This file is part of Calendar Event Booking Bundle.
 *
 * (c) Marko Cupic 2024 <m.cupic@gmx.ch>
 * @license MIT
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed with this source code.
 * @link https://github.com/markocupic/calendar-event-booking-bundle
 */

namespace Markocupic\CalendarEventBookingBundle\Controller\FrontendModule;

use Contao\CalendarEventsModel;
use Contao\Controller;
use Contao\CoreBundle\Controller\FrontendModule\AbstractFrontendModuleController;
use Contao\CoreBundle\Csrf\ContaoCsrfTokenManager;
use Contao\CoreBundle\DependencyInjection\Attribute\AsFrontendModule;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\CoreBundle\Routing\ScopeMatcher;
use Contao\ModuleModel;
use Contao\PageModel;
use Contao\StringUtil;
use Contao\Template;
use Markocupic\CalendarEventBookingBundle\Helper\NotificationHelper;
use Markocupic\CalendarEventBookingBundle\Model\CalendarEventsMemberModel;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;
use Terminal42\NotificationCenterBundle\NotificationCenter;

#[AsFrontendModule(CalendarEventBookingUnsubscribeFromEventModuleController::TYPE, category:'events', template: 'mod_calendar_event_booking_unsubscribe_from_event_module')]
class CalendarEventBookingUnsubscribeFromEventModuleController extends AbstractFrontendModuleController
{
    public const TYPE = 'calendar_event_booking_unsubscribe_from_event_module';

    protected ?CalendarEventsMemberModel $objEventMember = null;
    protected ?CalendarEventsModel $objEvent = null;
    protected array $errorMsg = [];
    protected bool $blnHasUnsubscribed = false;
    protected bool $hasError = false;

    public function __construct(
        private readonly ContaoFramework $framework,
        private readonly NotificationCenter $notificationCenter,
        private readonly NotificationHelper $notificationHelper,
        private readonly ScopeMatcher $scopeMatcher,
        private readonly TranslatorInterface $translator,
        private readonly ContaoCsrfTokenManager $csrfTokenManager,
    ) {
    }

    /**
     * @throws \Exception
     */
    public function __invoke(Request $request, ModuleModel $model, string $section, array $classes = null, PageModel $page = null): Response
    {
        // Is frontend
        if ($page instanceof PageModel && $this->scopeMatcher->isFrontendRequest($request)) {
            $page->noSearch = 1;

            $calendarEventsMemberModelAdapter = $this->framework->getAdapter(CalendarEventsMemberModel::class);
            $controllerAdapter = $this->framework->getAdapter(Controller::class);

            if ('true' !== $request->query->get('has_unsubscribed')) {
                $token = $request->query->get('bookingToken', false);

                $this->objEventMember = $calendarEventsMemberModelAdapter->findOneByBookingToken($token);

                if (!$token) {
                    $this->addError($this->translator->trans('ERR.invalidBookingToken', [], 'contao_default'));
                }

                if (!$this->hasError) {
                    if (null === $this->objEventMember) {
                        $this->addError($this->translator->trans('ERR.invalidBookingToken', [], 'contao_default'));
                    }
                }

                if (!$this->hasError) {
                    if (null === ($this->objEvent = $this->objEventMember->getRelated('pid'))) {
                        $this->addError($this->translator->trans('ERR.eventNotFound', [], 'contao_default'));
                    }
                }

                if (!$this->hasError) {
                    if (!$this->objEvent->enableDeregistration) {
                        $this->addError($this->translator->trans('ERR.eventUnsubscriptionNotAllowed', [$this->objEvent->title], 'contao_default'));
                    }
                }

                if (!$this->hasError) {
                    $blnLimitExpired = false;

                    // User has set a specific unsubscription limit timestamp, this has precedence
                    if (!empty($this->objEvent->unsubscribeLimitTstamp)) {
                        if (time() > $this->objEvent->unsubscribeLimitTstamp) {
                            $blnLimitExpired = true;
                        }
                    }
                    // We only have an unsubscription limit expressed in days before event start date
                    else {
                        $limit = !$this->objEvent->unsubscribeLimit > 0 ? 0 : $this->objEvent->unsubscribeLimit;

                        if (time() + $limit * 3600 * 24 > $this->objEvent->startDate) {
                            $blnLimitExpired = true;
                        }
                    }

                    if ($blnLimitExpired) {
                        $this->addError($this->translator->trans('ERR.unsubscriptionLimitExpired', [$this->objEvent->title], 'contao_default'));
                    }
                }

                if (!$this->hasError) {
                    // Delete data record and redirect
                    if ('tl_unsubscribe_from_event' === $request->request->get('FORM_SUBMIT')) {
                        $this->notify($this->objEventMember, $this->objEvent, $model);
                        $this->objEventMember->delete();

                        $href = sprintf(
                            '%s?has_unsubscribed=true&eid=%s',
                            $page->getFrontendUrl(),
                            $this->objEvent->id
                        );

                        $controllerAdapter->redirect($href);
                    }
                }
            }

            if ('true' === $request->query->get('has_unsubscribed')) {
                $this->blnHasUnsubscribed = true;
            }
        }

        // Call the parent method
        return parent::__invoke($request, $model, $section, $classes);
    }

    /**
     * @throws \Exception
     */
    protected function getResponse(Template $template, ModuleModel $model, Request $request): Response
    {
        $calendarEventsModelAdapter = $this->framework->getAdapter(CalendarEventsModel::class);

        if ($this->blnHasUnsubscribed) {
            $template->blnHasUnsubscribed = true;

            if (null !== ($objEvent = $calendarEventsModelAdapter->findByPk($request->query->get('eid')))) {
                $template->event = $objEvent;
            }
        } else {
            if ($this->hasError) {
                $template->errorMsg = $this->errorMsg;
            } else {
                $template->formId = 'tl_unsubscribe_from_event';
                $template->event = $this->objEvent;
                $template->calendar = $this->objEvent->getRelated('pid');
                $template->member = $this->objEventMember;
                $template->requestToken = $this->csrfTokenManager->getDefaultTokenValue();
            }
        }

        return $template->getResponse();
    }

    /**
     * @throws \Exception
     */
    protected function notify(CalendarEventsMemberModel $objEventMember, CalendarEventsModel $objEvent, ModuleModel $model): void
    {
        $stringUtilAdapter = $this->framework->getAdapter(StringUtil::class);

        if ($objEvent->enableDeregistration) {
            // Multiple notifications possible
            $arrNotifications = $stringUtilAdapter->deserialize($model->unsubscribeFromEventNotificationIds);

            if (!empty($arrNotifications) && \is_array($arrNotifications)) {
                // Get $arrToken from helper
                $arrTokens = $this->notificationHelper->getNotificationTokens($objEventMember);

                // Send notification (multiple notifications possible)
                foreach ($arrNotifications as $notificationId) {
                    $this->notificationCenter->sendNotification((int) $notificationId, $arrTokens);
                }
            }
        }
    }

    protected function addError(string $strMsg): void
    {
        $this->hasError = true;
        $this->errorMsg[] = $strMsg;
    }
}
