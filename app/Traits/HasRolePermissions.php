<?php

namespace App\Traits;

use Illuminate\Support\Facades\Auth;

trait HasRolePermissions
{
    /**
     * Проверка на роль менеджера
     */
    protected function isManager(): bool
    {
        return Auth::guard('employees')->user()->role === 'manager';
    }

    /**
     * Проверка на роль замерщика
     */
    protected function isSurveyor(): bool
    {
        return Auth::guard('employees')->user()->role === 'surveyor';
    }

    /**
     * Проверка на роль конструктора
     */
    protected function isConstructor(): bool
    {
        return Auth::guard('employees')->user()->role === 'constructor';
    }

    /**
     * Проверка на роль установщика
     */
    protected function isInstaller(): bool
    {
        return Auth::guard('employees')->user()->role === 'installer';
    }

    /**
     * Может ли пользователь управлять заказами (создание, редактирование, удаление)
     */
    protected function canManageOrders(): bool
    {
        return $this->isManager();
    }

    /**
     * Может ли пользователь просматривать заказы
     */
    protected function canViewOrders(): bool
    {
        return in_array(Auth::guard('employees')->user()->role, ['manager', 'surveyor', 'constructor', 'installer']);
    }

    /**
     * Может ли пользователь управлять договорами (создание, подписание, удаление)
     */
    protected function canManageContracts(): bool
    {
        return $this->isManager();
    }

    /**
     * Может ли пользователь просматривать договоры
     */
    protected function canViewContracts(): bool
    {
        return in_array(Auth::guard('employees')->user()->role, ['manager', 'surveyor', 'constructor']);
    }

    /**
     * Может ли пользователь управлять замерами
     */
    protected function canManageMeasurements(): bool
    {
        return in_array(Auth::guard('employees')->user()->role, ['manager', 'surveyor', 'constructor']);
    }

    /**
     * Может ли пользователь просматривать замеры
     */
    protected function canViewMeasurements(): bool
    {
        return in_array(Auth::guard('employees')->user()->role, ['manager', 'surveyor', 'constructor']);
    }

    /**
     * Может ли пользователь работать с корзиной (восстанавливать удаленные записи)
     */
    protected function canManageTrash(): bool
    {
        return $this->isManager();
    }

    /**
     * Может ли пользователь просматривать календарь
     */
    protected function canViewCalendar(): bool
    {
        return in_array(Auth::guard('employees')->user()->role, ['manager', 'surveyor', 'constructor', 'installer']);
    }

    /**
     * Может ли пользователь управлять установками
     */
    protected function canManageInstallations(): bool
    {
        return in_array(Auth::guard('employees')->user()->role, ['manager', 'installer']);
    }

    /**
     * Может ли пользователь просматривать установки
     */
    protected function canViewInstallations(): bool
    {
        return in_array(Auth::guard('employees')->user()->role, ['manager', 'installer', 'constructor']);
    }

    /**
     * Получить текущего пользователя
     */
    protected function getCurrentUser()
    {
        return Auth::guard('employees')->user();
    }

    /**
     * Получить роль текущего пользователя
     */
    protected function getCurrentUserRole(): string
    {
        return Auth::guard('employees')->user()->role;
    }
} 