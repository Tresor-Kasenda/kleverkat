<?php

declare(strict_types=1);

namespace App\Actions\Companies;

use App\Enums\TeamRole;
use App\Models\Company;
use App\Models\Team;
use App\Models\User;
use App\Notifications\Companies\CompanyManagerCredentials;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CreateCompanyWithManager
{
    public function handle(array $data): Company
    {
        $password = Str::password(12);

        [$company, $manager] = DB::transaction(function () use ($data, $password): array {
            $manager = User::create([
                'name' => $data['manager_name'],
                'email' => $data['manager_email'],
                'password' => $password,
            ]);

            $team = Team::findOrFail($data['team_id']);
            $team->members()->attach($manager, ['role' => TeamRole::Owner->value]);
            $manager->switchTeam($team);

            $company = Company::create([
                ...Arr::except($data, ['manager_name', 'manager_email']),
                'manager_id' => $manager->id,
            ]);

            return [$company, $manager];
        });

        $manager->notify(new CompanyManagerCredentials($company, $password));

        return $company;
    }
}
