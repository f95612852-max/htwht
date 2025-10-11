<?php

namespace App\Http\Controllers;

use App\Models\VerificationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class VerificationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $user = Auth::user();
        $verificationRequest = $user->verificationRequests()->latest()->first();
        
        return view('settings.verification', compact('verificationRequest'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'full_name' => 'required|string|max:255',
            'contact_info' => 'required|string|max:255',
            'identity_document' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120', // 5MB max
            'reason' => 'required|string|max:1000',
            'supporting_attachments' => 'nullable|array',
            'supporting_attachments.*' => 'string|url',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $user = Auth::user();

        // Check if user already has a pending request
        $existingRequest = $user->verificationRequests()
            ->where('status', 'pending')
            ->first();

        if ($existingRequest) {
            return back()->with('error', 'لديك طلب توثيق قيد المراجعة بالفعل.');
        }

        // Check if user is already verified
        if ($user->is_verified) {
            return back()->with('error', 'حسابك موثق بالفعل.');
        }

        // Store identity document
        $identityPath = null;
        if ($request->hasFile('identity_document')) {
            $identityPath = $request->file('identity_document')->store('verification/documents', 'public');
        }

        // Create verification request
        VerificationRequest::create([
            'user_id' => $user->id,
            'full_name' => $request->full_name,
            'contact_info' => $request->contact_info,
            'identity_document_path' => $identityPath,
            'reason' => $request->reason,
            'supporting_attachments' => $request->supporting_attachments ?? [],
            'status' => 'pending',
            'submitted_at' => now(),
        ]);

        return back()->with('success', 'تم إرسال طلب التوثيق بنجاح. قد يستغرق الأمر حتى 48 ساعة للمراجعة. تحقق من بريدك الإلكتروني.');
    }

    public function show(VerificationRequest $verificationRequest)
    {
        $this->authorize('view', $verificationRequest);
        
        return view('verification.show', compact('verificationRequest'));
    }

    // Admin methods
    public function adminIndex()
    {
        $this->authorize('admin');
        
        $requests = VerificationRequest::with('user')
            ->orderBy('submitted_at', 'desc')
            ->paginate(20);
            
        return view('admin.verification.index', compact('requests'));
    }

    public function adminShow(VerificationRequest $verificationRequest)
    {
        $this->authorize('admin');
        
        return view('admin.verification.show', compact('verificationRequest'));
    }

    public function approve(Request $request, VerificationRequest $verificationRequest)
    {
        $this->authorize('admin');
        
        $verificationRequest->approve(Auth::id(), $request->admin_notes);
        
        return back()->with('success', 'تم قبول طلب التوثيق وتم توثيق المستخدم.');
    }

    public function reject(Request $request, VerificationRequest $verificationRequest)
    {
        $this->authorize('admin');
        
        $validator = Validator::make($request->all(), [
            'admin_notes' => 'required|string|max:1000',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator);
        }
        
        $verificationRequest->reject(Auth::id(), $request->admin_notes);
        
        return back()->with('success', 'تم رفض طلب التوثيق.');
    }
}