<?php

namespace App\Services;

class ReservationService
{
    public function checkAvailability($facilityId, $startTime, $endTime)
    {
    }

    public function createReservation(array $data)
    {
    }

    public function approveReservation($reservationId)
    {
    }

    public function rejectReservation($reservationId, $reason)
    {
    }

    public function cancelReservation($reservationId, $reason)
    {
    }

    public function checkConflict($facilityId, $startTime, $endTime, $excludeId = null)
    {
    }

    public function validateTimeSlot($startTime, $endTime)
    {
    }
}
