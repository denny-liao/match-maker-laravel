<?php

namespace Src\Blog\Comment\Presentation\Requests;


class DonateCommentRequest extends CommentRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {

        return [
            ...parent::rules(),
            'name' => 'required|string|max:50',
            'amount' => 'required|integer|min:10',
            'channel' => 'required|string'
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Please write your name!',
            'name.max' => 'Name must be 50 characters or less.',
            'amount.min' => 'Amount must be 10 or more.',
            'channel' => '必須選擇金流通道。'
        ];
    }
}
