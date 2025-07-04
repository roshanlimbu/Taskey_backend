<?php

namespace App\Http\Controllers\MasterAdmin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class MasterAdminController extends Controller
{
    /**
     * Get comprehensive dashboard data for master admin
     */
    public function getDashboardData()
    {
        try {
            // User Statistics
            $totalUsers = User::count();
            $activeUsers = User::where('is_user_verified', true)->count();
            $newUsersThisMonth = User::whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count();

            // User distribution by role
            $usersByRole = User::select('role', DB::raw('count(*) as count'))
                ->groupBy('role')
                ->get()
                ->map(function ($item) {
                    $roleNames = [
                        0 => 'Master Admin',
                        1 => 'Super Admin',
                        2 => 'Admin',
                        3 => 'User'
                    ];
                    return [
                        'role' => $roleNames[$item->role] ?? 'Unknown',
                        'count' => $item->count
                    ];
                });

            // Project Statistics
            $totalProjects = Project::count();
            $activeProjects = Project::whereHas('tasks', function ($query) {
                $query->whereNotIn('status', ['completed', 'cancelled']);
            })->count();
            $projectsThisMonth = Project::whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count();

            // Task Statistics
            $totalTasks = Task::count();
            $completedTasks = Task::where('status', 'completed')->count();
            $pendingTasks = Task::where('status', 'pending')->count();
            $inProgressTasks = Task::where('status', 'in_progress')->count();
            $overdueTasks = Task::where('due_date', '<', now())
                ->whereNotIn('status', ['completed', 'cancelled'])
                ->count();

            // Growth Analytics (last 6 months)
            $growthData = [];
            for ($i = 5; $i >= 0; $i--) {
                $month = now()->subMonths($i);
                $growthData[] = [
                    'month' => $month->format('M Y'),
                    'users' => User::whereMonth('created_at', $month->month)
                        ->whereYear('created_at', $month->year)
                        ->count(),
                    'projects' => Project::whereMonth('created_at', $month->month)
                        ->whereYear('created_at', $month->year)
                        ->count(),
                    'tasks' => Task::whereMonth('created_at', $month->month)
                        ->whereYear('created_at', $month->year)
                        ->count()
                ];
            }

            // Recent Activity
            $recentUsers = User::latest()->limit(10)->get(['id', 'name', 'email', 'role', 'created_at']);
            $recentProjects = Project::latest()->limit(10)->get(['id', 'name', 'description', 'created_at']);

            // System Health Metrics
            $systemHealth = [
                'database_size' => $this->getDatabaseSize(),
                'total_storage_used' => $this->getStorageUsed(),
                'average_response_time' => $this->getAverageResponseTime()
            ];
            // get all companies data like name, email, phone, address
            $systemHealth['total_companies'] = DB::table('companies')->count();
            $systemHealth['companies'] = DB::table('companies')->get(['name', 'email', 'phone', 'address']);



            return response()->json([
                'status' => 'success',
                'data' => [
                    'user_stats' => [
                        'total_users' => $totalUsers,
                        'active_users' => $activeUsers,
                        'new_users_this_month' => $newUsersThisMonth,
                        'users_by_role' => $usersByRole
                    ],
                    'project_stats' => [
                        'total_projects' => $totalProjects,
                        'active_projects' => $activeProjects,
                        'projects_this_month' => $projectsThisMonth
                    ],
                    'task_stats' => [
                        'total_tasks' => $totalTasks,
                        'completed_tasks' => $completedTasks,
                        'pending_tasks' => $pendingTasks,
                        'in_progress_tasks' => $inProgressTasks,
                        'overdue_tasks' => $overdueTasks
                    ],
                    'growth_analytics' => $growthData,
                    'recent_activity' => [
                        'users' => $recentUsers,
                        'projects' => $recentProjects
                    ],
                    'system_health' => $systemHealth
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch dashboard data',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all users with advanced filtering and statistics
     */
    public function getAllUsersWithStats(Request $request)
    {
        try {
            $query = User::query();

            // Apply filters
            if ($request->has('role') && $request->role !== '') {
                $query->where('role', $request->role);
            }

            if ($request->has('verified') && $request->verified !== '') {
                $query->where('is_user_verified', $request->verified);
            }

            if ($request->has('search') && $request->search !== '') {
                $query->where(function ($q) use ($request) {
                    $q->where('name', 'like', '%' . $request->search . '%')
                        ->orWhere('email', 'like', '%' . $request->search . '%');
                });
            }

            // Get users with pagination
            $users = $query->with(['projects', 'tasks'])
                ->orderBy('created_at', 'desc')
                ->paginate(20);

            // Add user statistics
            $users->getCollection()->transform(function ($user) {
                $user->projects_count = $user->projects->count();
                $user->tasks_count = $user->tasks->count();
                $user->completed_tasks_count = $user->tasks->where('status', 'completed')->count();
                unset($user->projects, $user->tasks); // Remove relations to reduce payload size
                return $user;
            });

            return response()->json([
                'status' => 'success',
                'users' => $users
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch users',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get payment/billing analytics (placeholder for future implementation)
     */
    public function getPaymentAnalytics()
    {
        // This is a placeholder for payment analytics
        // You would implement actual payment logic based on your payment system
        return response()->json([
            'status' => 'success',
            'message' => 'Payment analytics not implemented yet',
            'data' => [
                'total_revenue' => 0,
                'monthly_revenue' => 0,
                'subscription_users' => 0,
                'trial_users' => 0,
                'churn_rate' => 0
            ]
        ]);
    }

    /**
     * Update user role (master admin only)
     */
    public function updateUserRole(Request $request, $userId)
    {
        try {
            $request->validate([
                'role' => 'required|integer|in:0,1,2,3'
            ]);

            $user = User::findOrFail($userId);

            // Prevent demotion of the last master admin
            if ($user->isMasterAdmin() && $request->role != 0) {
                $masterAdminCount = User::where('role', 0)->count();
                if ($masterAdminCount <= 1) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Cannot demote the last master admin'
                    ], 400);
                }
            }

            $user->role = $request->role;
            $user->save();

            return response()->json([
                'status' => 'success',
                'message' => 'User role updated successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update user role',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get system logs and activity
     */
    public function getSystemLogs()
    {
        try {
            // This would fetch actual system logs in a real implementation
            $logs = [
                [
                    'timestamp' => now()->subMinutes(5),
                    'level' => 'info',
                    'message' => 'User login successful',
                    'user_id' => 1
                ],
                [
                    'timestamp' => now()->subMinutes(10),
                    'level' => 'warning',
                    'message' => 'Failed login attempt',
                    'ip' => '192.168.1.100'
                ],
                [
                    'timestamp' => now()->subMinutes(15),
                    'level' => 'info',
                    'message' => 'New project created',
                    'project_id' => 5
                ]
            ];

            return response()->json([
                'status' => 'success',
                'logs' => $logs
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch system logs',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Helper method to get database size
     */
    private function getDatabaseSize()
    {
        try {
            $result = DB::select("SELECT 
                ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS 'DB Size in MB' 
                FROM information_schema.tables 
                WHERE table_schema = DATABASE()");
            return $result[0]->{'DB Size in MB'} ?? 0;
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Helper method to get storage used (placeholder)
     */
    private function getStorageUsed()
    {
        // This would calculate actual storage used by files
        return "250 MB";
    }

    /**
     * Helper method to get average response time (placeholder)
     */
    private function getAverageResponseTime()
    {
        // This would calculate actual average response time
        return "120ms";
    }
}
