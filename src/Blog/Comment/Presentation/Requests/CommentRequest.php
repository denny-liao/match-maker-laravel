<?php

namespace Src\Blog\Comment\Presentation\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CommentRequest extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'message' => 'required|string|max:255',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
           'message.required' => 'Please write something to comment!',
            'message.max' => 'Comments must be 255 characters or less.',
        ];
    }
}
