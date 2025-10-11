@if($user->is_verified)
    <i class="fas fa-check-circle text-primary ms-1" 
       title="حساب موثق" 
       data-bs-toggle="tooltip" 
       data-bs-placement="top"
       style="font-size: {{ $size ?? '14px' }}; color: #1da1f2 !important;"></i>
@endif