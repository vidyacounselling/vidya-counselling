@props(['name'=>'arrow'])
<svg class="icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
@switch($name)
@case('dashboard')<rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/>@break
@case('users')<circle cx="9" cy="8" r="4"/><path d="M2 21v-3a7 7 0 0114 0v3M17 4a4 4 0 010 8m2 3a6 6 0 013 5"/>@break
@case('blogs')<path d="M5 3h11l4 4v14H5zM8 11h9M8 15h9M8 18h5M15 3v5h5"/>@break
@case('eye')<path d="M2 12s4-7 10-7 10 7 10 7-4 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/>@break
@case('edit')<path d="M14 5l5 5M4 20l5-1L21 7l-5-5L4 14z"/>@break
@case('trash')<path d="M3 6h18M9 6V3h6v3M6 6l1 15h10l1-15M10 10v7M14 10v7"/>@break
@case('plus')<path d="M12 4v16M4 12h16"/>@break
@case('menu')<path d="M4 6h16M4 12h16M4 18h16"/>@break
@case('close')<path d="M5 5l14 14M19 5L5 19"/>@break
@case('logout')<path d="M9 3H3v18h6M12 12h10m-4-4l4 4-4 4"/>@break
@default<path d="M5 12h14m-5-5l5 5-5 5"/>
@endswitch
</svg>
