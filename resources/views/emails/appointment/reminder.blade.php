<x-mail::message>
# Hello {{ $citizenName }}!

This is a reminder that you have an appointment scheduled for tomorrow.

<x-mail::panel>
**Office:** {{ $officeName }}
**Date:** {{ $date }}
**Time:** {{ $startTime }} – {{ $endTime }}
</x-mail::panel>

> Please arrive 10 minutes early.

<x-mail::button :url="route('citizen.requests.index')">
View My Appointments
</x-mail::button>

Thanks,<br>
E-Services Platform Team
</x-mail::message>
