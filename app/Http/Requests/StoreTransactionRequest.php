<?php

namespace App\Http\Requests;

use App\Models\Account;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StoreTransactionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Auth::check() && Auth::user()->currentStore() !== null;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'account_id' => 'required|exists:accounts,id',
            'type' => 'required|in:income,expense',
            'amount' => 'required|numeric|min:0',
            'transaction_date' => 'required|date',
            'description' => 'nullable|string',
            'proof_file' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $store = Auth::user()->currentStore();
            
            if ($this->filled('account_id')) {
                $account = Account::find($this->account_id);
                
                if ($account && $account->store_id !== $store->id) {
                    $validator->errors()->add('account_id', 'Akun tidak ditemukan di toko Anda.');
                }
            }
        });
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'account_id.required' => 'Kategori akun wajib dipilih.',
            'account_id.exists' => 'Kategori akun tidak valid.',
            'type.required' => 'Tipe transaksi wajib dipilih.',
            'type.in' => 'Tipe transaksi harus income atau expense.',
            'amount.required' => 'Jumlah wajib diisi.',
            'amount.numeric' => 'Jumlah harus berupa angka.',
            'amount.min' => 'Jumlah tidak boleh negatif.',
            'transaction_date.required' => 'Tanggal transaksi wajib diisi.',
            'transaction_date.date' => 'Format tanggal tidak valid.',
            'proof_file.mimes' => 'File bukti harus berformat JPG, JPEG, PNG, atau PDF.',
            'proof_file.max' => 'Ukuran file maksimal 5MB.',
        ];
    }
}
