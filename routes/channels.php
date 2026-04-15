<?php

use App\Models\ServiceRequest;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

/*
|--------------------------------------------------------------------------
| Office live chat (citizen ↔ municipality staff, not tied to a request)
|--------------------------------------------------------------------------
*/
Broadcast::channel('office-chat.{officeId}.{citizenUserId}', function ($user, $officeId, $citizenUserId) {
    if ((int) $user->id === (int) $citizenUserId && $user->isCitizen()) {
        return true;
    }

    if ($user->isOfficeUser()
        && $user->governmentOffices()->whereKey($officeId)->exists()) {
        return true;
    }

    return false;
});

/*
|--------------------------------------------------------------------------
| Legacy: service request chat (kept if old clients still subscribe)
|--------------------------------------------------------------------------
*/
Broadcast::channel('service-requests.{serviceRequestId}', function ($user, $serviceRequestId) {
    $request = ServiceRequest::query()->find($serviceRequestId);

    if (! $request) {
        return false;
    }

    if ((int) $user->id === (int) $request->user_id) {
        return true;
    }

    if ($user->isOfficeUser()
        && $user->governmentOffices()->whereKey($request->government_office_id)->exists()) {
        return true;
    }

    return false;
});

Broadcast::channel('appointments.office.{officeId}', function ($user, $officeId) {
    if ($user->isCitizen()) {
        return true;
    }

    if ($user->isOfficeUser()
        && $user->governmentOffices()->whereKey($officeId)->exists()) {
        return true;
    }

    return false;
});
