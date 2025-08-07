<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TransferRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'recipient_id' => 'required|integer|exists:users,id|different:' . $this->route('senderId'),
            'amount' => 'required|numeric|gt:0',
        ];
    }
}
