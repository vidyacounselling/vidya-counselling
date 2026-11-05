<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
class SaveUserRequest extends FormRequest {
    public function authorize(): bool { return $this->user()?->isAdministrator()??false; }
    protected function prepareForValidation(): void { $this->merge(['email'=>mb_strtolower(trim((string)$this->email))]); }
    public function rules(): array { return [
        'name'=>['required','string','max:120'],'email'=>['required','email','max:254',Rule::unique('users')->ignore($this->route('user')?->id)],
        'password'=>[$this->isMethod('POST')?'required':'nullable','string','min:12','max:128','confirmed'],
        'role'=>['required',Rule::in(['administrator','editor'])],'active'=>['required','boolean'],
        'bio'=>['nullable','string','max:3000'],'credentials'=>['nullable','string','max:255']]; }
}
