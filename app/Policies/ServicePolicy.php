<?php
namespace App\Policies;
use App\Models\{Service,User};
class ServicePolicy {public function view(User $u,Service $s):bool{return $u->isStaff();}public function update(User $u,Service $s):bool{return $u->isStaff();}public function delete(User $u,Service $s):bool{return $u->isStaff();}}
