<?php

namespace Src\Matchmaking\Room\Presentation\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class SubmitRoomVoteRequest extends FormRequest
{
    /**
     * @return array<string, string>
     */
    public function rules(): array
    {
        // sometimes 選填
        // .* 萬用字元:陣列底下的每一個項目逐一進行檢查
        // distinct 陣列裡的數字絕對不能重複
        return [
            'target_user_ids' => 'sometimes|array',
            'target_user_ids.*' => 'required|integer|distinct',
        ];
    }
}
