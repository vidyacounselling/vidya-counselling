<?php
namespace App\Policies;
use App\Models\{Inquiry,User};
class InquiryPolicy {public function viewAny(User $u):bool{return $u->canManageInquiries();}public function view(User $u,Inquiry $i):bool{return $u->canManageInquiries();}public function update(User $u,Inquiry $i):bool{return $u->canManageInquiries();}public function delete(User $u,Inquiry $i):bool{return $u->isAdministrator();}}
