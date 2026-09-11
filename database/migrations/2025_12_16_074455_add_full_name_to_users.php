<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('full_name')->after('id')->nullable();
        });

        // Populate full_name from existing data
        User::all()->each(function ($user) {
            $middle = $user->middle_initial ? $user->middle_initial.'. ' : '';
            $user->full_name = $user->first_name.' '.$middle.$user->last_name;
            $user->save();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('full_name');
        });
    }
};
