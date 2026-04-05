<?php

namespace App\Domain\Settings\Controllers;

use App\Domain\Settings\Requests\ProfileDeleteRequest;
use App\Domain\Settings\Requests\ProfileUpdateRequest;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController
{
    /**
     * Show the user's profile settings page.
     */
    public function edit(Request $request): Response
    {
        return Inertia::render('settings/Profile', [
            'mustVerifyEmail' => $request->user() instanceof MustVerifyEmail,
            'status' => $request->session()->get('status'),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        try {
            DB::transaction(function () use ($request) {
                $request->user()->fill($request->validated());

                if ($request->user()->isDirty('email')) {
                    $request->user()->email_verified_at = null;
                }

                $request->user()->save();
            });

            return to_route('profile.edit');
        } catch (\Throwable $e) {
            Log::error('Failed to update profile', [
                'user_uid' => $request->user()->uid,
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', 'Erro ao atualizar perfil.');
        }
    }

    /**
     * Delete the user's profile.
     */
    public function destroy(ProfileDeleteRequest $request): RedirectResponse
    {
        try {
            $user = $request->user();

            DB::transaction(function () use ($user) {
                Auth::logout();
                $user->delete();
            });

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect('/');
        } catch (\Throwable $e) {
            Log::error('Failed to delete profile', [
                'user_uid' => $request->user()->uid,
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', 'Erro ao excluir perfil.');
        }
    }
}
