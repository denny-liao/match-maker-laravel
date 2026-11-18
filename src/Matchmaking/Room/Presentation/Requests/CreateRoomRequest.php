<?php

namespace Src\Matchmaking\Room\Presentation\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class CreateRoomRequest extends FormRequest
{
    /**
     * @return array<string, string>
     */
    public function rules(): array
    {
        return [
            'title' => 'required|string|max:100',
        ];
    }
}
