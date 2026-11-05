<?php
namespace App\Services;
use App\Models\{User,Activity};
use Illuminate\Support\Facades\{DB,Hash};
use Illuminate\Validation\ValidationException;

class StaffAccounts {
    public function save(User $actor, ?User $target, array $data, bool $trash=false): User {
        abort_unless($actor->isAdministrator(),403);
        return DB::transaction(function() use($actor,$target,$data,$trash) {
            DB::table('staff_locks')->where('id',1)->lockForUpdate()->first();
            $current=$target?User::withTrashed()->whereKey($target->id)->lockForUpdate()->firstOrFail():new User;
            $remove=$trash || (isset($data['active'])&&!$data['active']) || (($data['role']??$current->role)!=='administrator');
            if($current->exists && $current->id===$actor->id && ($trash || (isset($data['active'])&&!$data['active'])))
                throw ValidationException::withMessages(['active'=>'You cannot delete or deactivate your own account.']);
            if($current->exists && $current->isAdministrator() && $remove && User::where('role','administrator')->where('active',true)->count()<=1)
                throw ValidationException::withMessages(['role'=>'Keep at least one active Administrator.']);
            if($trash) { $current->delete(); $action='Staff moved to trash'; }
            else {
                foreach(['name','email','bio','credentials','role','active'] as $key) if(array_key_exists($key,$data)) $current->$key=$data[$key];
                $current->is_admin=$current->role==='administrator';
                if(!empty($data['password'])) $current->password=Hash::make($data['password']);
                $current->save(); $action='Staff account saved';
            }
            if($current->exists && (!$current->active || $trash || !empty($data['password']))) DB::table('sessions')->where('user_id',$current->id)->delete();
            Activity::record($action,'User #'.$current->id);
            return $current;
        });
    }
}
