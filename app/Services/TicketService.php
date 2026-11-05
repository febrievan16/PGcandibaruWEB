<?php

namespace App\Services;

use App\Models\AdminNotification;
use App\Models\Order;
use App\Models\Ticket;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TicketService
{
    /**
     * Issue tickets for a paid order idempotently (FR-07).
     *
     * @return Collection<int, Ticket>
     */
    public function issueTicketsForOrder(Order $order): Collection
    {
        return DB::transaction(function () use ($order): Collection {
            $existing = $order->relationLoaded('tickets')
                ? $order->tickets
                : $order->tickets()->get();

            if ($existing->count() >= $order->ticket_qty) {
                return $existing;
            }

            $toCreate = $order->ticket_qty - $existing->count();
            for ($i = 0; $i < $toCreate; $i++) {
                $seq = $existing->count() + $i + 1;
                $ticketCode = sprintf(
                    'TKT-%s-%02d-%s',
                    substr(str_replace('GT-', '', $order->order_code), 0, 12),
                    $seq,
                    strtoupper(Str::random(4))
                );

                $signature = substr(hash_hmac('sha256', $ticketCode . '|' . $order->order_code, (string) config('app.key')), 0, 16);
                $qrPayload = "GULATOUR|{$order->order_code}|{$ticketCode}|{$signature}";

                $ticket = Ticket::create([
                    'order_id' => $order->id,
                    'ticket_code' => $ticketCode,
                    'qr_payload' => $qrPayload,
                    'status' => 'valid',
                ]);

                $existing->push($ticket);
            }

            return $existing;
        });
    }

    /**
     * Validate and check in a ticket via QR payload or ticket code (FR-14).
     *
     * @return array{valid: bool, code: string, message: string, ticket: ?Ticket}
     */
    public function validateAndCheckIn(string $input): array
    {
        $trimmed = trim($input);

        $ticket = Ticket::with(['order.schedule.event'])
            ->where('qr_payload', $trimmed)
            ->orWhere('ticket_code', $trimmed)
            ->first();

        if (! $ticket) {
            return [
                'valid' => false,
                'code' => 'TICKET_NOT_FOUND',
                'message' => 'Tiket atau kode QR tidak ditemukan dalam sistem.',
                'ticket' => null,
            ];
        }

        if ($ticket->order->status !== 'paid') {
            return [
                'valid' => false,
                'code' => 'ORDER_NOT_PAID',
                'message' => 'Pesanan untuk tiket ini belum berstatus Lunas (' . $ticket->order->statusLabel() . ').',
                'ticket' => $ticket,
            ];
        }

        if ($ticket->status === 'used') {
            $checkedAt = $ticket->checked_in_at?->format('d M Y H:i') ?? '-';

            return [
                'valid' => false,
                'code' => 'TICKET_ALREADY_USED',
                'message' => "Tiket {$ticket->ticket_code} sudah digunakan pada {$checkedAt}.",
                'ticket' => $ticket,
            ];
        }

        if ($ticket->status === 'void') {
            return [
                'valid' => false,
                'code' => 'TICKET_VOID',
                'message' => "Tiket {$ticket->ticket_code} telah dibatalkan (void).",
                'ticket' => $ticket,
            ];
        }

        $ticket->update([
            'status' => 'used',
            'checked_in_at' => now(),
        ]);

        return [
            'valid' => true,
            'code' => 'CHECKIN_SUCCESS',
            'message' => "Tiket {$ticket->ticket_code} berhasil divalidasi dan check-in tercatat.",
            'ticket' => $ticket,
        ];
    }

    /**
     * Generate a deterministic, high-contrast SVG QR representation for a ticket payload.
     */
    public function renderSvgQr(string $payload, int $size = 168): string
    {
        $gridSize = 21;
        $hash = hash('sha256', $payload) . hash('sha256', 'salt:' . $payload);
        $bits = '';
        for ($i = 0; $i < strlen($hash); $i++) {
            $bits .= str_pad(base_convert($hash[$i], 16, 2), 4, '0', STR_PAD_LEFT);
        }

        $matrix = array_fill(0, $gridSize, array_fill(0, $gridSize, 0));

        $drawFinder = function (int $rowOffset, int $colOffset) use (&$matrix): void {
            for ($r = 0; $r < 7; $r++) {
                for ($c = 0; $c < 7; $c++) {
                    $isBorder = ($r === 0 || $r === 6 || $c === 0 || $c === 6);
                    $isInner = ($r >= 2 && $r <= 4 && $c >= 2 && $c <= 4);
                    $matrix[$rowOffset + $r][$colOffset + $c] = ($isBorder || $isInner) ? 1 : 0;
                }
            }
        };

        $drawFinder(0, 0);
        $drawFinder(0, $gridSize - 7);
        $drawFinder($gridSize - 7, 0);

        $bitIndex = 0;
        for ($r = 0; $r < $gridSize; $r++) {
            for ($c = 0; $c < $gridSize; $c++) {
                $inTopLeft = ($r < 8 && $c < 8);
                $inTopRight = ($r < 8 && $c >= $gridSize - 8);
                $inBottomLeft = ($r >= $gridSize - 8 && $c < 8);

                if ($inTopLeft || $inTopRight || $inBottomLeft) {
                    continue;
                }

                if ($r === 6 || $c === 6) {
                    $matrix[$r][$c] = (($r + $c) % 2 === 0) ? 1 : 0;
                    continue;
                }

                $matrix[$r][$c] = (int) $bits[$bitIndex % strlen($bits)];
                $bitIndex++;
            }
        }

        $cell = 8;
        $padding = 12;
        $viewBox = ($gridSize * $cell) + ($padding * 2);
        $rects = [];

        for ($r = 0; $r < $gridSize; $r++) {
            for ($c = 0; $c < $gridSize; $c++) {
                if ($matrix[$r][$c] === 1) {
                    $x = $padding + ($c * $cell);
                    $y = $padding + ($r * $cell);
                    $rects[] = "<rect x=\"{$x}\" y=\"{$y}\" width=\"{$cell}\" height=\"{$cell}\" fill=\"#2A211A\"/>";
                }
            }
        }

        $rectsHtml = implode('', $rects);

        return <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" width="{$size}" height="{$size}" viewBox="0 0 {$viewBox} {$viewBox}" role="img" aria-label="QR Code E-Ticket">
    <rect width="{$viewBox}" height="{$viewBox}" fill="#FFFFFF" stroke="#E3D9C9" stroke-width="2"/>
    {$rectsHtml}
</svg>
SVG;
    }
}

