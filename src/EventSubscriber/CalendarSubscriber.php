<?php

namespace App\EventSubscriber;

use App\Controller\StateController;
use App\Repository\BookingRepository;
use CalendarBundle\Entity\Event;
use CalendarBundle\Event\SetDataEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class CalendarSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly BookingRepository $bookingRepository,
        private readonly UrlGeneratorInterface $router
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            SetDataEvent::class => 'onCalendarSetData',
        ];
    }

    public function onCalendarSetData(SetDataEvent $setDataEvent): void
    {
        $start = $setDataEvent->getStart();
        $end = $setDataEvent->getEnd();
        $filters = $setDataEvent->getFilters();

        // Modify the query to fit to your entity and needs
        // Change booking.beginAt by your start date property
        $bookings = $this->bookingRepository
            ->createQueryBuilder('booking')
            ->where('booking.start BETWEEN :start and :end OR booking.end BETWEEN :start and :end')
            ->setParameter('start', $start->format('Y-m-d H:i:s'))
            ->setParameter('end', $end->format('Y-m-d H:i:s'))
            ->getQuery()
            ->getResult()
        ;

        foreach ($bookings as $booking) {
            // this create the events with your data (here booking data) to fill calendar
            $bookingEvent = new Event(
                $booking->getCustomer()->getFullName()  . ' - ' . $booking->getState()->getName(),
                $booking->getStart(),
                $booking->getEnd(), // If the end date is null or not defined, a all day event is created.
                null,
                ['stateId' => $booking->getState()->getId()],
            );


            $backgroundcolor = new StateController()->getColorByState($bookingEvent->getOption('stateId'));
            $textColor = new StateController()->getTextColorByState($bookingEvent->getOption('stateId'));
            /*
             * Add custom options to events
             *
             * For more information see: https://fullcalendar.io/docs/event-object
             */
            $bookingEvent->setOptions([
                'backgroundColor' => $backgroundcolor,
                'borderColor' => $backgroundcolor,
                'textColor' => $textColor,
            ]);
            $bookingEvent->addOption(
                'url',
                $this->router->generate('app_booking_showIframe', [
                    'id' => $booking->getId(),
                ])
            );

            // finally, add the event to the CalendarEvent to fill the calendar
            $setDataEvent->addEvent($bookingEvent);
        }
    }
}