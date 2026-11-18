<?php

namespace Src\Matchmaking\Room\Presentation\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class AssignParticipantNumberRequest extends FormRequest
{
    /**
     * @return array<string, string>
     */
    public function rules(): array
    {
        return [
            'number' => 'required|integer|min:1|max:65535',
        ];
    }
}
