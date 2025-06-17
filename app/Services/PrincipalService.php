<?php

namespace App\Services;

use App\Models\Principal;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Exception;

class PrincipalService
{
    public function index()
    {
        try {
            Log::info('Fetching all principals for index');
            $principals = Principal::all();

            $formattedPrincipals = $principals->map(function ($principal, $key) {
                return [
                    'count' => $key + 1,
                    'image' => '<img class="img img-fluid img-rounded" alt="User Avatar" src="' . ($principal->getFirstMediaUrl('avatar', 'thumb') ?: asset('dist/img/avatar.png')) . '" style="width: 50px;">',
                    'principal_name' => $principal->full_name_with_extension,
                    'contact' => $principal->contact,
                    'email' => $principal->email,
                    'role' => ucfirst($principal->user->role),
                    'action' => '<a href="' . route('admin.updatePrincipal', ['principalId' => $principal->id]) . '" type="button" class="btn btn-md btn-primary" title="Update"><i class="fa fa-edit"></i></a>',
                ];
            })->toArray();

            Log::info('Successfully fetched principals', ['count' => count($formattedPrincipals)]);
            return $formattedPrincipals;
        } catch (Exception $e) {
            Log::error('Failed to fetch principals', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return [];
        }
    }

    public function store(array $data)
    {
        try {
            Log::info('Attempting to store new principal', ['email' => $data['email']]);

            $principal = DB::transaction(function () use ($data) {
                $user = User::create([
                    'username' => $data['email'],
                    'password' => Hash::make($data['email']),
                    'role' => User::ROLE_PRINCIPAL,
                    'is_active' => true,
                ]);

                return $user->principal()->create($data);
            });

            Log::info('Principal stored successfully', ['principal_id' => $principal->id, 'user_id' => $principal->user_id]);

            return [
                'valid' => true,
                'msg' => 'Principal added successfully.',
                'principal' => $principal,
            ];
        } catch (Exception $e) {
            Log::error('Failed to store principal', [
                'data' => $data,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return [
                'valid' => false,
                'msg' => 'Failed to add principal. Please try again later.',
            ];
        }
    }

    public function update($principalId, array $data)
    {
        try {
            Log::info('Attempting to update principal', ['principal_id' => $principalId]);

            $principal = DB::transaction(function () use ($principalId, $data) {
                $principal = Principal::findOrFail($principalId);
                $principal->update($data);
                return $principal;
            });

            Log::info('Principal updated successfully', ['principal_id' => $principalId]);

            return [
                'valid' => true,
                'msg' => 'Principal updated successfully.',
                'principal' => $principal,
            ];
        } catch (Exception $e) {
            Log::error('Failed to update principal', [
                'principal_id' => $principalId,
                'data' => $data,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return [
                'valid' => false,
                'msg' => 'Failed to update principal. Please try again later.',
            ];
        }
    }

    public function updateAvatar($principalId, $file)
    {
        try {
            Log::info('Attempting to update principal avatar', ['principal_id' => $principalId]);

            $imageUrl = DB::transaction(function () use ($principalId, $file) {
                $principal = Principal::findOrFail($principalId);
                $principal->addMedia($file)->toMediaCollection('avatar');
                return $principal->getFirstMediaUrl('avatar', 'thumb');
            });

            Log::info('Principal avatar updated successfully', ['principal_id' => $principalId, 'image_url' => $imageUrl]);

            return [
                'valid' => true,
                'msg' => 'Avatar updated successfully.',
                'image' => $imageUrl,
            ];
        } catch (Exception $e) {
            Log::error('Failed to update principal avatar', [
                'principal_id' => $principalId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return [
                'valid' => false,
                'msg' => 'Failed to update avatar. Please try again later.',
            ];
        }
    }
}
