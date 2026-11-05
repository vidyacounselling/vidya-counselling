<?php
namespace App\Policies;
use App\Models\User;
class UserPolicy {
    public function before(User $actor): ?bool { return $actor->isAdministrator() ? true : false; }
    public function viewAny(User $actor): bool { return false; }
}
