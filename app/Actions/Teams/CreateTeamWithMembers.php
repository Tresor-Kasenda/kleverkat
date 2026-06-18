<?php

declare(strict_types=1);

namespace App\Actions\Teams;

use App\Models\Team;
use App\Models\User;
use App\Notifications\Teams\TeamMemberCredentials;
use Illuminate\Support\Facades\DB;
use Throwable;

class CreateTeamWithMembers
{
    /**
     * Create a partner team together with its member accounts and notify them.
     *
     * @param  array<string, mixed>  $data
     *
     * @throws Throwable
     */
    public function handle(array $data): Team
    {
        /** @var array{0: Team, 1: array<int, array{user: User, password: string}>} $result */
        $result = DB::transaction(function () use ($data): array {
            $team = Team::query()
                ->create([
                    'name' => $data['name'],
                    'slug' => $data['slug'],
                    'is_personal' => false,
                ]);

            $credentials = [];

            foreach ($data['members'] ?? [] as $member) {

                $user = User::query()
                    ->create([
                        'name' => $member['name'],
                        'email' => $member['email'],
                        'password' => $member['password'],
                    ]);

                $team->members()->attach($user, ['role' => $member['role']]);
                $user->switchTeam($team);

                $credentials[] = ['user' => $user, 'password' => $member['password']];
            }

            return [$team, $credentials];
        });

        [$team, $credentials] = $result;

        foreach ($credentials as $credential) {
            $credential['user']->notify(new TeamMemberCredentials($team, $credential['password']));
        }

        return $team;
    }
}
