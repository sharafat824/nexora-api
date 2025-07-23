<?php

namespace App\Http\Controllers\Admin;

use App\Models\Deposit;
use Illuminate\Http\Request;
use App\Services\ReferralService;
use App\Http\Controllers\Controller;
use App\Http\Resources\DepositResource;
use App\Notifications\DepositStatusUpdated;

class AdminDepositController extends Controller
{
    protected $referralService;
    public function __construct(ReferralService $referralService)
    {
        $this->referralService = $referralService;
    }

    public function index(Request $request)
    {
        $query = Deposit::with('user')->orderBy('created_at', 'desc');

        if ($request->filled('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }
        if ($request->filled('amount_min')) {
            $query->where('amount', '>=', $request->amount_min);
        }
        if ($request->filled('amount_max')) {
            $query->where('amount', '<=', $request->amount_max);
        }



        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by user name or email
        if ($request->filled('search')) {
            $query->whereHas('user', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                    ->orWhere('email', 'like', '%' . $request->search . '%');
            });
        }

        $deposits = $query->paginate($request->get('page_size', 10));

        return success([
            'data' => DepositResource::collection($deposits),
            'current_page' => $deposits->currentPage(),
            'last_page' => $deposits->lastPage(),
            'from' => $deposits->firstItem(),
            'to' => $deposits->lastItem(),
            'total' => $deposits->total(),
        ], 'Deposits fetched');
    }



    public function updateDepositStatus(Request $request, Deposit $deposit)
    {
        $validated = $request->validate([
            'status' => 'required|in:completed,rejected',
            'rejection_reason' => 'nullable|string|max:255',
            'admin_notes' => 'nullable|string|max:500'
        ]);

        // Check if deposit is already processed
        if ($deposit->status !== 'pending') {
            return response()->json([
                'message' => 'Deposit already processed',
                'current_status' => $deposit->status
            ], 400);
        }

        // Update deposit status
        $deposit->status = $validated['status'];
        $deposit->rejection_reason = $validated['rejection_reason'] ?? null;
        $deposit->admin_notes = $validated['admin_notes'] ?? null;
        $deposit->processed_at = now();
        $deposit->save();

        // Handle completed deposit
        if ($validated['status'] === 'completed') {
            // Credit user's wallet
            $deposit->user->wallet->deposit($deposit->amount);

            // Record transaction
            $deposit->user->transactions()->create([
                'amount' => $deposit->amount,
                'type' => 'deposit',
                'status' => 'completed',
                'reference_id' => $deposit->id,
                // 'notes' => $validated['admin_notes'] ?? null
            ]);

            // Distribute referral commissions if not already done
            if (!$deposit->user->is_commission_distributed) {
                $this->referralService->distributeCommissions($deposit->user, $deposit->amount);
                $deposit->user->is_commission_distributed = true;
                $deposit->user->save();
            }
        } else {
            // For rejected deposits, create transaction record
            $deposit->user->transactions()->create([
                'amount' => $deposit->amount,
                'type' => 'deposit',
                'status' => 'rejected',
                'reference_id' => $deposit->id,
                //'notes' => $validated['rejection_reason'] ?? null
            ]);
        }

        // Send notification to user
        $deposit->user->notify(new DepositStatusUpdated($deposit));

        return response()->json([
            'message' => 'Deposit status updated successfully',
            'deposit' => new DepositResource($deposit)
        ]);
    }


    public function store(Request $request)
    {
        $data = $request->validate([
            'user_id' => 'required|exists:users,id',
            'amount' => 'required|numeric|min:1',
            'status' => 'in:pending,completed,rejected'
        ]);

        $deposit = new Deposit([
            'user_id' => $data['user_id'],
            'amount' => $data['amount'],
            'status' => $data['status'] ?? 'completed'
        ]);

        $deposit->save();

        if ($deposit->status === 'completed') {
            $deposit->user->wallet->deposit($deposit->amount);

            $deposit->user->transactions()->create([
                'amount' => $deposit->amount,
                'type' => 'deposit',
                'status' => 'completed',
                'reference_id' => $deposit->id
            ]);
        }

        return success(new DepositResource($deposit), 'Manual deposit added');
    }

    public function export(Request $request): StreamedResponse
    {
        $deposits = Deposit::with('user')->latest()->get();

        $headers = [
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=deposits.csv",
        ];

        $callback = function () use ($deposits) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['ID', 'User', 'Email', 'Amount', 'Status', 'Date']);

            foreach ($deposits as $deposit) {
                fputcsv($file, [
                    $deposit->id,
                    $deposit->user?->name,
                    $deposit->user?->email,
                    $deposit->amount,
                    $deposit->status,
                    $deposit->created_at,
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }


}
