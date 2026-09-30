<?php

namespace App\Contracts;

use App\Models\Payment;

interface PaymentGateway
{
    /**
     * @return array{
     *     reference: string,
     *     status: string,
     *     payload: array<string, mixed>
     * }
     */
    public function initiate(
        Payment $payment,
        string $phone,
        string $idempotencyKey,
    ): array;

    /**
     * @return array{
     *     reference: string,
     *     status: string,
     *     amount_minor: int,
     *     currency: string,
     *     payload: array<string, mixed>
     * }
     */
    public function verify(string $reference): array;
}