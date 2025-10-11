@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">
                        <i class="fas fa-dollar-sign text-success"></i>
                        الأرباح والإحصائيات
                    </h4>
                </div>
                <div class="card-body">
                    <!-- Earnings Overview -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="card bg-primary text-white">
                                <div class="card-body text-center">
                                    <h5>إجمالي المشاهدات</h5>
                                    <h3>{{ number_format($earnings->total_views) }}</h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-success text-white">
                                <div class="card-body text-center">
                                    <h5>إجمالي الأرباح</h5>
                                    <h3>${{ number_format($earnings->total_earnings, 2) }}</h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-warning text-white">
                                <div class="card-body text-center">
                                    <h5>الأرباح المعلقة</h5>
                                    <h3>${{ number_format($earnings->pending_earnings, 2) }}</h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-info text-white">
                                <div class="card-body text-center">
                                    <h5>الأرباح المدفوعة</h5>
                                    <h3>${{ number_format($earnings->paid_earnings, 2) }}</h3>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- How Earnings Work -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">كيف تعمل الأرباح؟</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <h6><i class="fas fa-eye text-primary"></i> نظام المشاهدات</h6>
                                    <ul>
                                        <li>تحصل على <strong>$0.30</strong> لكل <strong>1000 مشاهدة</strong></li>
                                        <li>يتم حساب المشاهدات الفريدة فقط</li>
                                        <li>مشاهدة واحدة لكل مستخدم في اليوم</li>
                                        <li>تحديث الأرباح كل ساعة</li>
                                    </ul>
                                </div>
                                <div class="col-md-6">
                                    <h6><i class="fas fa-chart-line text-success"></i> إحصائياتك</h6>
                                    <ul>
                                        <li>المشاهدات المطلوبة للربح التالي: <strong>{{ $earnings->getViewsNeededForNextEarning() }}</strong></li>
                                        <li>معدل الربح: <strong>${{ $earnings->getEarningsPerThousandViews() }}</strong> لكل 1000 مشاهدة</li>
                                        <li>آخر تحديث: {{ $earnings->last_calculated_at ? $earnings->last_calculated_at->diffForHumans() : 'لم يتم التحديث بعد' }}</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Views Statistics -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">إحصائيات المشاهدات الأخيرة</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3 text-center">
                                    <h6>اليوم</h6>
                                    <h4 class="text-primary">{{ number_format($recentViews['today']) }}</h4>
                                </div>
                                <div class="col-md-3 text-center">
                                    <h6>أمس</h6>
                                    <h4 class="text-secondary">{{ number_format($recentViews['yesterday']) }}</h4>
                                </div>
                                <div class="col-md-3 text-center">
                                    <h6>هذا الأسبوع</h6>
                                    <h4 class="text-info">{{ number_format($recentViews['this_week']) }}</h4>
                                </div>
                                <div class="col-md-3 text-center">
                                    <h6>هذا الشهر</h6>
                                    <h4 class="text-success">{{ number_format($recentViews['this_month']) }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Top Performing Posts -->
                    @if(count($topPosts) > 0)
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">أفضل المنشورات أداءً</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>المنشور</th>
                                            <th>عدد المشاهدات</th>
                                            <th>الأرباح المقدرة</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($topPosts as $post)
                                        <tr>
                                            <td>
                                                <a href="/p/{{ $post['id'] }}" target="_blank">
                                                    {{ Str::limit($post['caption'] ?? 'منشور بدون وصف', 50) }}
                                                </a>
                                            </td>
                                            <td>{{ number_format($post['view_count']) }}</td>
                                            <td>${{ number_format(($post['view_count'] / 1000) * 0.3, 2) }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Tips for Increasing Earnings -->
                    <div class="card mt-4">
                        <div class="card-header">
                            <h5 class="mb-0">نصائح لزيادة الأرباح</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <h6><i class="fas fa-lightbulb text-warning"></i> نصائح للمحتوى</h6>
                                    <ul>
                                        <li>انشر محتوى عالي الجودة ومثير للاهتمام</li>
                                        <li>استخدم الهاشتاجات المناسبة</li>
                                        <li>انشر في الأوقات التي يكون فيها جمهورك نشطاً</li>
                                        <li>تفاعل مع متابعيك في التعليقات</li>
                                    </ul>
                                </div>
                                <div class="col-md-6">
                                    <h6><i class="fas fa-users text-info"></i> نصائح للتفاعل</h6>
                                    <ul>
                                        <li>تابع حسابات مشابهة لمحتواك</li>
                                        <li>شارك في المجتمعات ذات الصلة</li>
                                        <li>اطلب من أصدقائك مشاركة منشوراتك</li>
                                        <li>انشر بانتظام للحفاظ على التفاعل</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Auto-refresh earnings data every 5 minutes
setInterval(function() {
    fetch('/api/earnings')
        .then(response => response.json())
        .then(data => {
            // Update the earnings display
            console.log('Earnings updated:', data);
        })
        .catch(error => console.error('Error updating earnings:', error));
}, 300000); // 5 minutes
</script>
@endsection