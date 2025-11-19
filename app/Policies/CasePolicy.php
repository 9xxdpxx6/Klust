<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\CaseModel;
use App\Models\User;

class CasePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('cases.view');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, CaseModel $case): bool
    {
        // Админ и учитель могут видеть все (если есть право)
        if ($user->hasAnyRole(['admin', 'teacher'])) {
            return $user->hasPermissionTo('cases.view');
        }

        // Партнер может видеть только свои кейсы
        if ($user->hasRole('partner')) {
            return $user->partnerProfile?->partner_id === $case->partner_id;
        }

        // Студент может видеть только активные кейсы
        if ($user->hasRole('student')) {
            return $case->status === 'active';
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('cases.create');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, CaseModel $case): bool
    {
        // Админ и учитель могут редактировать все (если есть право)
        if ($user->hasAnyRole(['admin', 'teacher'])) {
            return $user->hasPermissionTo('cases.update');
        }

        // Партнер может редактировать только свои кейсы
        if ($user->hasRole('partner')) {
            return $user->partnerProfile?->partner_id === $case->partner_id
                && $user->hasPermissionTo('cases.update');
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, CaseModel $case): bool
    {
        // Только админ может удалять (если есть право)
        if ($user->hasRole('admin')) {
            return $user->hasPermissionTo('cases.delete');
        }

        return false;
    }

    /**
     * Determine whether the user can archive the model.
     */
    public function archive(User $user, CaseModel $case): bool
    {
        // Админ и учитель могут архивировать все
        if ($user->hasAnyRole(['admin', 'teacher'])) {
            return $user->hasPermissionTo('cases.update');
        }

        // Партнер может архивировать только свои кейсы
        if ($user->hasRole('partner')) {
            return $user->partnerProfile?->partner_id === $case->partner_id
                && $user->hasPermissionTo('cases.update');
        }

        return false;
    }

    /**
     * Determine whether the user can view applications for the case.
     */
    public function viewApplications(User $user, CaseModel $case): bool
    {
        // Админ и учитель могут видеть все заявки
        if ($user->hasAnyRole(['admin', 'teacher'])) {
            return $user->hasPermissionTo('cases.view');
        }

        // Партнер может видеть заявки только на свои кейсы
        if ($user->hasRole('partner')) {
            return $user->partnerProfile?->partner_id === $case->partner_id;
        }

        return false;
    }

    /**
     * Determine whether the user can approve applications.
     */
    public function approveApplication(User $user, CaseModel $case): bool
    {
        // Админ и учитель могут одобрять заявки на все кейсы
        if ($user->hasAnyRole(['admin', 'teacher'])) {
            return $user->hasPermissionTo('cases.approve');
        }

        // Партнер может одобрять заявки только на свои кейсы
        if ($user->hasRole('partner')) {
            return $user->partnerProfile?->partner_id === $case->partner_id
                && $user->hasPermissionTo('cases.approve');
        }

        return false;
    }

    /**
     * Determine whether the user can reject applications.
     */
    public function rejectApplication(User $user, CaseModel $case): bool
    {
        // Аналогично approveApplication
        return $this->approveApplication($user, $case);
    }
}
