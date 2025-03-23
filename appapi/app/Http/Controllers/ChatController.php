<?php
namespace App\Http\Controllers;

use App\Events\MessageSent;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ChatController extends Controller
{
    /**
     * Send a message in the chat
     */
    public function sendMessage(Request $request)
    {
        try {
            Log::info('Send message request received', [
                'request_data' => $request->all(),
                'auth_check' => Auth::check(),
                'auth_id' => Auth::id(),
                'headers' => $request->headers->all()
            ]);
            
            $request->validate([
                'receiver_id' => 'required|exists:users,id',
                'message' => 'required|string',
            ]);

            // Get the authenticated user ID as sender
            $senderId = Auth::id();
            if (!$senderId) {
                Log::warning('User not authenticated when sending message');
                return response()->json(['error' => 'User not authenticated'], 401);
            }

            // Create the message
            $message = Message::create([
                'sender_id' => $senderId,
                'receiver_id' => $request->receiver_id,
                'message' => $request->message,
                'is_read' => false, // Mark as unread initially
            ]);

            Log::info('Message created successfully', ['message_id' => $message->id]);

            // Broadcast the message event with message content as a string for backward compatibility
            event(new MessageSent($message->message, $senderId, $request->receiver_id));

            return response()->json([
                'status' => 'success',
                'message' => $message
            ]);
        } catch (\Exception $e) {
            Log::error('Error sending message', [
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all(),
                'auth_check' => Auth::check()
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Error sending message: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get messages between a user and an admin
     */
    public function getMessages($userId, $adminId)
    {
        try {
            // Verify authentication
            $authUser = Auth::user();
            if (!$authUser) {
                return response()->json(['error' => 'User not authenticated'], 401);
            }

            // Determine if the authenticated user is the user or admin in this conversation
            $isUser = $authUser->id == $userId;
            $isAdmin = $authUser->id == $adminId;

            // Make sure the authenticated user is either the user or admin in this conversation
            if (!$isUser && !$isAdmin) {
                return response()->json([
                    'error' => 'Unauthorized to view these messages'
                ], 403);
            }

            // Get messages between these users
            $messages = Message::where(function ($query) use ($userId, $adminId) {
                $query->where('sender_id', $userId)
                      ->where('receiver_id', $adminId);
            })->orWhere(function ($query) use ($userId, $adminId) {
                $query->where('sender_id', $adminId)
                      ->where('receiver_id', $userId);
            })->orderBy('created_at', 'asc')->get();

            // Mark messages as read if the user is the receiver
            if ($isUser) {
                Message::where('sender_id', $adminId)
                      ->where('receiver_id', $userId)
                      ->where('is_read', false)
                      ->update(['is_read' => true]);
            } elseif ($isAdmin) {
                Message::where('sender_id', $userId)
                      ->where('receiver_id', $adminId)
                      ->where('is_read', false)
                      ->update(['is_read' => true]);
            }

            return response()->json([
                'messages' => $messages
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting messages: ' . $e->getMessage());
            return response()->json([
                'error' => 'Error getting messages: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all users who have chatted with the admin
     */
    public function getUsersWithChats()
    {
        try {
            // Ensure the user is authenticated and is an admin
            $admin = Auth::user();
            if (!$admin) {
                return response()->json(['error' => 'User not authenticated'], 401);
            }

            // Check if the authenticated user is an admin
            if (strtolower($admin->role) !== 'admin') {
                return response()->json(['error' => 'Only admins can view chat users'], 403);
            }

            // Get all users who have sent messages to or received messages from this admin
            $userIds = Message::where('sender_id', $admin->id)
                            ->orWhere('receiver_id', $admin->id)
                            ->pluck('sender_id')
                            ->merge(Message::where('sender_id', $admin->id)
                                          ->orWhere('receiver_id', $admin->id)
                                          ->pluck('receiver_id'))
                            ->unique()
                            ->filter(function ($id) use ($admin) {
                                return $id != $admin->id;
                            });

            $users = User::whereIn('id', $userIds)->get();

            // For each user, get the unread count and last message
            $usersWithChatInfo = $users->map(function ($user) use ($admin) {
                // Count unread messages from this user to the admin
                $unreadCount = Message::where('sender_id', $user->id)
                                    ->where('receiver_id', $admin->id)
                                    ->where('is_read', false)
                                    ->count();

                // Get the last message between this user and the admin
                $lastMessage = Message::where(function ($query) use ($user, $admin) {
                                    $query->where('sender_id', $user->id)
                                          ->where('receiver_id', $admin->id);
                                })
                                ->orWhere(function ($query) use ($user, $admin) {
                                    $query->where('sender_id', $admin->id)
                                          ->where('receiver_id', $user->id);
                                })
                                ->orderBy('created_at', 'desc')
                                ->first();

                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'profile_image' => $user->url,
                    'unread_count' => $unreadCount,
                    'last_message' => $lastMessage ? [
                        'message' => $lastMessage->message,
                        'time' => $lastMessage->created_at,
                        'is_from_admin' => $lastMessage->sender_id === $admin->id
                    ] : null
                ];
            });

            return response()->json([
                'users' => $usersWithChatInfo
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting users with chats: ' . $e->getMessage());
            return response()->json([
                'error' => 'Error getting users with chats: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get count of unread messages for the authenticated user
     */
    public function getUnreadCount()
    {
        try {
            $userId = Auth::id();
            if (!$userId) {
                return response()->json(['error' => 'User not authenticated'], 401);
            }

            $unreadCount = Message::where('receiver_id', $userId)
                                 ->where('is_read', false)
                                 ->count();

            return response()->json([
                'unread_count' => $unreadCount
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting unread count: ' . $e->getMessage());
            return response()->json([
                'error' => 'Error getting unread count: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all admins for chat selection
     */
    public function getAdmins()
    {
        try {
            $userId = Auth::id();
            if (!$userId) {
                return response()->json(['error' => 'User not authenticated'], 401);
            }

            // Get all users with Admin role
            $admins = User::where('role', 'admin')->get(['id', 'name', 'email', 'url']);

            return response()->json([
                'admins' => $admins
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting admins: ' . $e->getMessage());
            return response()->json([
                'error' => 'Error getting admins: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mark messages from a specific sender as read
     */
    public function markMessagesAsRead($senderId)
    {
        try {
            $userId = Auth::id();
            if (!$userId) {
                return response()->json(['error' => 'User not authenticated'], 401);
            }

            // Mark all messages from sender to this user as read
            $updated = Message::where('sender_id', $senderId)
                             ->where('receiver_id', $userId)
                             ->where('is_read', false)
                             ->update(['is_read' => true]);

            return response()->json([
                'status' => 'success',
                'updated_count' => $updated
            ]);
        } catch (\Exception $e) {
            Log::error('Error marking messages as read: ' . $e->getMessage());
            return response()->json([
                'error' => 'Error marking messages as read: ' . $e->getMessage()
            ], 500);
        }
    }
}
