<?php

namespace App\Http\Controllers\Settings;

use App\Models\Account\Photo;
use App\Helpers\AccountHelper;
use App\Helpers\StorageHelper;
use App\Models\Contact\Document;
use App\Http\Controllers\Controller;

class StorageController extends Controller
{
    /**
     * Get all the information about the account in terms of storage.
     */
    public function index()
    {
        $documents = Document::where('account_id', auth()->user()->account_id)->get();
        $photos = Photo::where('account_id', auth()->user()->account_id)->get();
        /** @var \Illuminate\Support\Collection<array-key, \Illuminate\Database\Eloquent\Model> */
        $documents = collect($documents);
        $elements = $documents->concat($photos)->sortByDesc('created_at');

        // size is in bytes in the database
        $currentAccountSize = StorageHelper::getAccountStorageSize(auth()->user()->account);

        if ($currentAccountSize != 0) {
            $currentAccountSize = round($currentAccountSize / 1000000);
        }

        // correspondingPercent
        $percentUsage = round($currentAccountSize * 100 / config('monica.max_storage_size'));

        $accountHasLimitations = AccountHelper::hasLimitations(auth()->user()->account);

        return view('settings.storage.index')
            ->withAccountHasLimitations($accountHasLimitations)
            ->withElements($elements)
            ->withCurrentAccountSize($currentAccountSize)
            ->withAccountLimit(config('monica.max_storage_size'))
            ->withPercentUsage($percentUsage);
    }
}
