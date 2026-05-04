<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Support\Facades\Validator;

class MoveStageRequestTest extends TestCase
{
    public function test_to_stage_is_required(): void
    {
        $request = new \App\Http\Requests\MoveStageRequest();
        $validator = Validator::make(
            [],
            $request->rules()
        );

        $this->assertFalse($validator->passes());
    }

    public function test_to_stage_must_be_valid_value(): void
    {
        $request = new \App\Http\Requests\MoveStageRequest();
        $validator = Validator::make(
            ['to_stage' => 'invalid_stage'],
            $request->rules()
        );

        $this->assertFalse($validator->passes());
    }

    public function test_to_stage_accepts_all_valid_values(): void
    {
        $request = new \App\Http\Requests\MoveStageRequest();
        $stages = ['applied', 'psychotest', 'hr_iv', 'user_iv', 'final', 'offer', 'hired', 'not_qualified'];

        foreach ($stages as $stage) {
            $validator = Validator::make(
                ['to_stage' => $stage],
                $request->rules()
            );

            $this->assertTrue($validator->passes(), "Failed for stage: {$stage}");
        }
    }

    public function test_status_is_nullable(): void
    {
        $request = new \App\Http\Requests\MoveStageRequest();
        $validator = Validator::make(
            ['to_stage' => 'applied'],
            $request->rules()
        );

        $this->assertTrue($validator->passes());
    }

    public function test_status_must_be_valid(): void
    {
        $request = new \App\Http\Requests\MoveStageRequest();
        $validator = Validator::make(
            ['to_stage' => 'applied', 'status' => 'invalid'],
            $request->rules()
        );

        $this->assertFalse($validator->passes());
    }

    public function test_note_is_nullable(): void
    {
        $request = new \App\Http\Requests\MoveStageRequest();
        $validator = Validator::make(
            ['to_stage' => 'applied', 'note' => 'Some notes'],
            $request->rules()
        );

        $this->assertTrue($validator->passes());
    }

    public function test_score_is_nullable_numeric(): void
    {
        $request = new \App\Http\Requests\MoveStageRequest();
        $validator = Validator::make(
            ['to_stage' => 'applied', 'score' => 'abc'],
            $request->rules()
        );

        $this->assertFalse($validator->passes());
    }

    public function test_valid_data_passes(): void
    {
        $request = new \App\Http\Requests\MoveStageRequest();
        $validator = Validator::make(
            [
                'to_stage' => 'hr_iv',
                'status' => 'passed',
                'note' => 'Good candidate',
                'score' => 85.5,
            ],
            $request->rules()
        );

        $this->assertTrue($validator->passes());
    }

    public function test_authorize_allows_valid_roles(): void
    {
        $request = new \App\Http\Requests\MoveStageRequest();
        
        $user = new \App\Models\User();
        $user->role = 'admin';
        $request->setUserResolver(fn() => $user);
        $this->assertTrue($request->authorize());
        
        $user->role = 'hr';
        $this->assertTrue($request->authorize());
        
        $user->role = 'superadmin';
        $this->assertTrue($request->authorize());
        
        $user->role = 'pelamar';
        $this->assertFalse($request->authorize());
        
        $request->setUserResolver(fn() => null);
        $this->assertFalse($request->authorize());
    }
}
