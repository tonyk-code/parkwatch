<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Services\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;

class CustomerPaymentController extends Controller
{
    public function show(
        Request $request,
        int $session,
    ): JsonResponse {
        $parkingSession = $request->user()
            ->parkingSessions()
            ->with([
                'site:id,name,code',
                'payments' => fn($query) => $query
                    ->latest('id')
                    ->select([
                        'id',
                        'session_id',
                        'amount_minor',
                        'currency',
                        'method',
                        'status',
                        'gateway_reference',
                        'paid_at',
                    ]),
            ])
            ->findOrFail($session);

        $remaining = max(
            0,
            (int) $parkingSession->amount_due
            - (int) $parkingSession->amount_paid,
        );

        $latestPayment = $parkingSession->payments->first();

        return response()->json([
            'session' => [
                'id' => $parkingSession->id,
                'reference_code' => $parkingSession->reference_code,
                'status' => $parkingSession->status->value,
                'amount_due' => (int) $parkingSession->amount_due,
                'amount_paid' => (int) $parkingSession->amount_paid,
                'remaining' => $remaining,
                'currency' => $parkingSession->currency,
                'site' => [
                    'id' => $parkingSession->site->id,
                    'name' => $parkingSession->site->name,
                    'code' => $parkingSession->site->code,
                ],
            ],

            'payment' => $latestPayment ? [
                'id' => $latestPayment->id,
                'amount' => (int) $latestPayment->amount_minor,
                'currency' => $latestPayment->currency,
                'method' => $latestPayment->method,
                'status' => $latestPayment->status,
                'gateway_reference' => $latestPayment->gateway_reference,
                'paid_at' => $latestPayment->paid_at,
            ] : null,
        ]);
    }

    public function store(
        Request $request,
        PaymentService $paymentService,
    ): JsonResponse {
        $validated = $request->validate([
            'session_id' => ['required', 'integer'],
            'phone' => ['required', 'string', 'max:30'],
            'idempotency_key' => ['required', 'string', 'max:100'],
        ]);

        $session = $request->user()
            ->parkingSessions()
            ->whereKey($validated['session_id'])
            ->firstOrFail();

        try {
            $payment = $paymentService->createPayment(
                session: $session,
                phone: $validated['phone'],
                idempotencyKey: $validated['idempotency_key'],
            );
        } catch (RuntimeException $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
            ], 422);
        }

        return response()->json([
            'payment' => [
                'id' => $payment->id,
                'amount' => $payment->amount_minor,
                'currency' => $payment->currency,
                'method' => $payment->method,
                'status' => $payment->status,
                'gateway_reference' => $payment->gateway_reference,
                'paid_at' => $payment->paid_at,
            ],
        ], 201);
    }

    public function showPayment(
        Request $request,
        int $payment,
    ): JsonResponse {
        $paymentRecord = Payment::query()
            ->whereKey($payment)
            ->whereHas('session', function ($query) use ($request) {
                $query->where('user_id', $request->user()->id);
            })
            ->with('session:id,reference_code,status,amount_due,amount_paid,currency')
            ->firstOrFail();

        return response()->json([
            'payment' => [
                'id' => $paymentRecord->id,
                'session_id' => $paymentRecord->session_id,
                'amount' => $paymentRecord->amount_minor,
                'currency' => $paymentRecord->currency,
                'method' => $paymentRecord->method,
                'status' => $paymentRecord->status,
                'gateway_reference' => $paymentRecord->gateway_reference,
                'paid_at' => $paymentRecord->paid_at,
            ],
            'session' => [
                'id' => $paymentRecord->session->id,
                'reference_code' => $paymentRecord->session->reference_code,
                'status' => $paymentRecord->session->status,
                'amount_due' => $paymentRecord->session->amount_due,
                'amount_paid' => $paymentRecord->session->amount_paid,
                'remaining' => max(
                    0,
                    (int) $paymentRecord->session->amount_due
                    - (int) $paymentRecord->session->amount_paid
                ),
                'currency' => $paymentRecord->session->currency,
            ],
        ]);
    }
}