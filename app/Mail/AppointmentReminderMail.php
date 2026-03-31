<?php

namespace App\Mail;

use App\Models\Appointment;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AppointmentReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Appointment $appointment) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Appointment Reminder — Tomorrow',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.appointment.reminder',
            with: [
                'citizenName' => $this->appointment->citizen->name,
                'officeName'  => $this->appointment->governmentOffice->name,
                'date'        => $this->appointment->timeSlot->date->format('l, d M Y'),
                'startTime'   => Carbon::parse($this->appointment->timeSlot->start_time)->format('H:i'),
                'endTime'     => Carbon::parse($this->appointment->timeSlot->end_time)->format('H:i'),
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
