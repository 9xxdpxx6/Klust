<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\CaseApplication;
use App\Models\User;

class CaseApplicationPolicy
{
    /**
     * Determine whether the user can view the application.
     */
    public function view(User $user, CaseApplication $application): bool
    {
        // Лидер заявки
        if ($application->leader_id === $user->id) {
            return true;
        }

        // Член команды
        if ($application->teamMembers()->where('user_id', $user->id)->exists()) {
            return true;
        }

        // Партнер, которому принадлежит кейс
        if ($user->hasRole('partner')) {
            return $user->partnerProfile?->partner_id === $application->case->partner_id;
        }

        // Админ и учитель
        if ($user->hasAnyRole(['admin', 'teacher'])) {
            return $user->hasPermissionTo('cases.view');
        }

        return false;
    }

    /**
     * Determine whether the user can update the application.
     */
    public function update(User $user, CaseApplication $application): bool
    {
        // Только лидер может обновлять заявку
        return $application->leader_id === $user->id
            && $application->status->name === 'pending';
    }

    /**
     * Determine whether the user can delete the application.
     */
    public function delete(User $user, CaseApplication $application): bool
    {
        // Только лидер может отозвать заявку
        return $application->leader_id === $user->id
            && $application->status->name === 'pending';
    }

    /**
     * Determine whether the user can add team members.
     */
    public function addTeamMember(User $user, CaseApplication $application): bool
    {
        // Только лидер может добавлять участников
        return $application->leader_id === $user->id
            && $application->status->name === 'pending';
    }

    /**
     * Determine whether the user can view the team.
     */
    public function viewTeam(User $user, CaseApplication $application): bool
    {
        // Лидер или член команды
        if ($application->leader_id === $user->id) {
            return true;
        }

        if ($application->teamMembers()->where('user_id', $user->id)->exists()) {
            return true;
        }

        // Партнер, которому принадлежит кейс
        if ($user->hasRole('partner')) {
            return $user->partnerProfile?->partner_id === $application->case->partner_id;
        }

        // Админ и учитель
        if ($user->hasAnyRole(['admin', 'teacher'])) {
            return $user->hasPermissionTo('cases.view');
        }

        return false;
    }
}
