@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">
                        <i class="fas fa-check-circle text-primary"></i>
                        طلب التوثيق بالعلامة الزرقاء
                    </h4>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger">
                            {{ session('error') }}
                        </div>
                    @endif

                    @if(auth()->user()->is_verified)
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle"></i>
                            <strong>تهانينا!</strong> حسابك موثق بالعلامة الزرقاء.
                        </div>
                    @elseif($verificationRequest && $verificationRequest->isPending())
                        <div class="alert alert-info">
                            <i class="fas fa-clock"></i>
                            <strong>طلبك قيد المراجعة</strong><br>
                            تم إرسال طلب التوثيق في {{ $verificationRequest->submitted_at->format('Y-m-d H:i') }}<br>
                            قد يستغرق الأمر حتى 48 ساعة للمراجعة. تحقق من بريدك الإلكتروني.
                        </div>
                    @elseif($verificationRequest && $verificationRequest->isRejected())
                        <div class="alert alert-danger">
                            <i class="fas fa-times-circle"></i>
                            <strong>تم رفض طلب التوثيق</strong><br>
                            السبب: {{ $verificationRequest->admin_notes }}<br>
                            يمكنك تقديم طلب جديد بعد تصحيح المشاكل المذكورة.
                        </div>
                    @endif

                    @if(!auth()->user()->is_verified && (!$verificationRequest || $verificationRequest->isRejected()))
                        <div class="mb-4">
                            <h5>ما هو التوثيق بالعلامة الزرقاء؟</h5>
                            <p class="text-muted">
                                العلامة الزرقاء تؤكد أن الحساب أصلي ومهم للمجتمع. تظهر العلامة بجانب اسمك في جميع أنحاء المنصة.
                            </p>
                        </div>

                        <form method="POST" action="{{ route('verification.store') }}" enctype="multipart/form-data">
                            @csrf
                            
                            <div class="mb-3">
                                <label for="full_name" class="form-label">الاسم الكامل *</label>
                                <input type="text" class="form-control @error('full_name') is-invalid @enderror" 
                                       id="full_name" name="full_name" value="{{ old('full_name') }}" required>
                                @error('full_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="contact_info" class="form-label">البريد الإلكتروني أو رقم الهاتف *</label>
                                <input type="text" class="form-control @error('contact_info') is-invalid @enderror" 
                                       id="contact_info" name="contact_info" value="{{ old('contact_info') }}" required>
                                @error('contact_info')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="identity_document" class="form-label">هوية المستخدم (صورة البطاقة) *</label>
                                <input type="file" class="form-control @error('identity_document') is-invalid @enderror" 
                                       id="identity_document" name="identity_document" accept=".jpg,.jpeg,.png,.pdf" required>
                                <div class="form-text">يُقبل JPG, PNG, PDF - حد أقصى 5 ميجابايت</div>
                                @error('identity_document')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="reason" class="form-label">سبب التوثيق *</label>
                                <textarea class="form-control @error('reason') is-invalid @enderror" 
                                          id="reason" name="reason" rows="4" required>{{ old('reason') }}</textarea>
                                <div class="form-text">اشرح لماذا تستحق التوثيق (مثل: شخصية عامة، فنان، كاتب، إلخ)</div>
                                @error('reason')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label">مرفقات داعمة (اختيارية)</label>
                                <div id="attachments-container">
                                    <div class="input-group mb-2">
                                        <input type="url" class="form-control" name="supporting_attachments[]" 
                                               placeholder="رابط لموقعك الشخصي، أعمالك، أو ملفك الشخصي">
                                        <button type="button" class="btn btn-outline-secondary" onclick="addAttachment()">
                                            <i class="fas fa-plus"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="form-text">أضف روابط لمواقعك، أعمالك، أو ملفاتك الشخصية الأخرى</div>
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-paper-plane"></i>
                                    إرسال طلب التوثيق
                                </button>
                            </div>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function addAttachment() {
    const container = document.getElementById('attachments-container');
    const div = document.createElement('div');
    div.className = 'input-group mb-2';
    div.innerHTML = `
        <input type="url" class="form-control" name="supporting_attachments[]" 
               placeholder="رابط إضافي">
        <button type="button" class="btn btn-outline-danger" onclick="removeAttachment(this)">
            <i class="fas fa-minus"></i>
        </button>
    `;
    container.appendChild(div);
}

function removeAttachment(button) {
    button.parentElement.remove();
}
</script>
@endsection