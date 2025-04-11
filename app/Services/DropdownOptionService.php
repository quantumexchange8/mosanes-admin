<?php

namespace App\Services;

use App\Models\User;
use App\Models\Group;
use App\Models\Country;
use App\Models\Transaction;
use App\Models\GroupHasUser;
use App\Models\SettingLeverage;
use Illuminate\Support\Collection;
use Illuminate\Support\Carbon;

class DropdownOptionService
{
    public function getUplines(): Collection
    {
        return User::whereIn('role', ['agent', 'member'])
            ->select('id', 'name')
            ->get()
            ->map(function ($user) {
                return [
                    'value' => $user->id,
                    'name' => $user->name,
                    'profile_photo' => $user->getFirstMediaUrl('profile_photo')
                ];
            });
    }

    public function getCountries(): Collection
    {
        return Country::get()->map(function ($country) {
            return [
                'id' => $country->id,
                'name' => $country->name,
                'phone_code' => $country->phone_code,
            ];
        });
    }

    public function getGroups(): Collection
    {
        return Group::get()
            ->map(function ($group) {
                return [
                    'value' => $group->id,
                    'name' => $group->name,
                    'color' => $group->color,
                ];
            });
    }

    public function getLeverages(): Collection
    {
        $leverages = SettingLeverage::where('status', 'active')
                    ->get()
                    ->map(function ($leverage) {
                        return [
                            'name' => $leverage->display,
                            'value' => $leverage->value,
                        ];
                    });
        $leverages->prepend(['name' => 'Free', 'value' => 0]);
        return $leverages;
    }

    public function getAgents(): Collection
    {
        $has_group = GroupHasUser::pluck('user_id');

        $users = User::where('role', 'agent')
            ->whereNotIn('id', $has_group)
            ->select('id', 'name')
            ->get()
            ->map(function ($user) {
                return [
                    'value' => $user->id,
                    'name' => $user->name,
                    'profile_photo' => $user->getFirstMediaUrl('profile_photo')
                ];
            });

        return $users;
    }

    public function getTransactionMonths(): Collection
    {
        // Fetch the created_at dates of all transactions
        $transactionDates = Transaction::pluck('created_at');

        // Map the dates to the desired format and remove duplicates
        $months = $transactionDates
            ->map(function ($date) {
                // Extract only the "F Y" format for uniqueness
                return Carbon::parse($date)->format('m/Y');
            })
            ->reverse()
            ->unique()
            ->values();
    
        // Add the current month at the end if it's not already in the list
        $currentMonth = Carbon::now()->format('m/Y');
        if (!$months->contains($currentMonth)) {
            $months->prepend($currentMonth);
        }

        // Add custom date ranges at the top
        $additionalRanges = collect([
            'last_week', 
            'last_2_week', 
            'last_3_week', 
        ]);

        $months = $additionalRanges->merge($months);
    
        return $months;
    }

}
