<?php

namespace App\Http\Controllers;

use App\Enums\TalentRole;
use App\Http\Requests\TalentRequest;
use App\Models\Talent;
use App\Services\Contracts\TalentAyonSyncServiceInterface;
use Exception;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Controller to manage Talents and synchronize them with AYON.
 */
class TalentsController extends Controller
{
    /**
     * @param TalentAyonSyncServiceInterface $ayonSync Service for syncing talents with AYON
     */
    public function __construct(private readonly TalentAyonSyncServiceInterface $ayonSync) {}

    /**
     * Display a paginated list of talents.
     *
     * @return Response
     */
    public function index(): Response
    {
        /** @var LengthAwarePaginator<Talent> $talents */
        $talents = Talent::orderByDesc('created_at')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Talents/Index', [
            'talents' => $talents,
        ]);
    }

    /**
     * Show the form to create a new talent.
     *
     * @return Response
     */
    public function create(): Response
    {
        return Inertia::render('Talents/Create', [
            'roles' => TalentRole::options(),
        ]);
    }

    /**
     * Store a newly created talent and sync it with AYON.
     *
     * @param TalentRequest $request
     * @return RedirectResponse
     *
     * @throws Exception
     */
    public function store(TalentRequest $request): RedirectResponse
    {
        DB::beginTransaction();
        try {
            /** @var Talent $talent */
            $talent = Talent::create($request->validated());

            $this->ayonSync->sync($talent, true);

            $talent->markAsSynced();

            DB::commit();

            return redirect()->route('talents.index')->withSuccess('Talent created!');
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to create talent', ['error' => $e->getMessage()]);
            return redirect()->back()->withError($e->getMessage());
        }
    }

    /**
     * Show the form for editing a talent.
     *
     * @param int $id
     * @return Response|RedirectResponse
     *
     * @throws Exception
     */
    public function edit(int $id): Response|RedirectResponse
    {
        try {
            /** @var Talent $talent */
            $talent = Talent::findOrFail($id);

            return Inertia::render('Talents/Edit', [
                'talent' => $talent,
                'roles'  => TalentRole::options(),
            ]);
        } catch (Exception $e) {
            Log::error('Failed to load talent for editing', ['talent_id' => $id, 'error' => $e->getMessage()]);
            return redirect()->back()->withErrors($e->getMessage());
        }
    }

    /**
     * Update a talent and sync changes with AYON.
     *
     * @param TalentRequest $request
     * @param int $id
     * @return RedirectResponse
     *
     * @throws Exception
     */
    public function update(TalentRequest $request, int $id): RedirectResponse
    {
        DB::beginTransaction();
        try {
            /** @var Talent $talent */
            $talent = Talent::findOrFail($id);

            $talent->update($request->validated());

            $this->ayonSync->sync($talent, false, $request->input('ayon_sync_status'));

            DB::commit();

            return redirect()->route('talents.index')->withSuccess('Talent updated!');
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to update talent', ['talent_id' => $id, 'error' => $e->getMessage()]);
            return redirect()->back()->withErrors($e->getMessage());
        }
    }

    /**
     * Delete a talent locally and from AYON.
     *
     * @param int $id
     * @return RedirectResponse
     *
     * @throws Exception
     */
    public function destroy(int $id): RedirectResponse
    {
        DB::beginTransaction();
        try {
            /** @var Talent $talent */
            $talent = Talent::findOrFail($id);

            $this->ayonSync->delete($talent);

            $talent->delete();

            DB::commit();

            return redirect()->route('talents.index')->withSuccess('Talent deleted!');
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to delete talent', ['talent_id' => $id, 'error' => $e->getMessage()]);
            return redirect()->back()->withErrors($e->getMessage());
        }
    }
}


// namespace App\Http\Controllers;

// use App\Enums\TalentRole;
// use App\Enums\TalentStatus;
// use App\Http\Requests\TalentRequest;
// use App\Models\Talent;
// use Benjamin\AyonConnector\Contracts\AyonClientInterface;
// use Exception;
// use Illuminate\Contracts\Pagination\LengthAwarePaginator;
// use Illuminate\Http\RedirectResponse;
// use Illuminate\Support\Facades\DB;
// use Illuminate\Support\Facades\Log;
// use Inertia\Inertia;
// use Inertia\Response;

// class TalentsController extends Controller
// {
//     private AyonClientInterface $ayonClient;

//     /**
//      * TalentsController constructor.
//      *
//      * @param AyonClientInterface $ayonClient
//      */
//     public function __construct(AyonClientInterface $ayonClient)
//     {
//         $this->ayonClient = $ayonClient;
//     }

//     /**
//      * Display a listing of talents.
//      *
//      * @return Response
//      */
//     public function index(): Response
//     {
//         /** @var LengthAwarePaginator $talents */
//         $talents = Talent::orderByDesc('created_at')
//             ->paginate(10)
//             ->withQueryString();

//         return Inertia::render('Talents/Index', [
//             'talents' => $talents,
//         ]);
//     }

//     /**
//      * Show the form for creating a new talent.
//      *
//      * @return Response
//      */
//     public function create(): Response
//     {
//         return Inertia::render('Talents/Create', [
//             'roles' => TalentRole::options(),
//         ]);
//     }

//     /**
//      * Store a newly created talent and sync with AYON.
//      *
//      * @param TalentRequest $request
//      * @return RedirectResponse
//      *
//      * @throws Exception
//      */
//     public function store(TalentRequest $request): RedirectResponse
//     {
//         DB::beginTransaction();

//         try {
//             $talent = Talent::create($request->validated());
//             $talent->setAyonName();

//             $this->ayonClient->createUser($talent->toAyonUser(), $talent->ayon_name);

//             $talent->markAsSynced();

//             DB::commit();

//             Log::info('Talent created successfully', ['talent_id' => $talent->id]);

//             return redirect()->route('talents.index')->withSuccess('Talent created!');
//         } catch (Exception $e) {
//             DB::rollBack();
//             Log::error('Failed to create talent', ['error' => $e->getMessage()]);

//             return redirect()->back()->withError($e->getMessage());
//         }
//     }

//     /**
//      * Show the form for editing a talent.
//      *
//      * @param int $id
//      * @return Response|RedirectResponse
//      *
//      * @throws Exception
//      */
//     public function edit(int $id): Response|RedirectResponse
//     {
//         try {
//             $talent = Talent::findOrFail($id);

//             return Inertia::render('Talents/Edit', [
//                 'talent' => $talent,
//                 'roles'  => TalentRole::options(),
//             ]);
//         } catch (Exception $e) {
//             Log::error('Failed to load talent for editing', ['talent_id' => $id, 'error' => $e->getMessage()]);
//             return redirect()->back()->withErrors($e->getMessage());
//         }
//     }

//     /**
//      * Update a specified talent and sync changes with AYON.
//      *
//      * @param TalentRequest $request
//      * @param int $id
//      * @return RedirectResponse
//      *
//      * @throws Exception
//      */
//     public function update(TalentRequest $request, int $id): RedirectResponse
//     {
//         DB::beginTransaction();

//         try {
//             $talent = Talent::findOrFail($id);

//             if($talent->ayon_sync_status == TalentStatus::PENDING->value){
//                 $talent->setAyonName();
//                 $this->ayonClient->createUser($talent->toAyonUser(), $talent->ayon_name);
//             }

//             if ($request->input('ayon_sync_status') === TalentStatus::INACTIVE->value) {
//                 $this->ayonClient->deactivateUser($talent->ayon_name);
//             } else {
//                 $this->ayonClient->updateUser($talent->toAyonUser(), $talent->ayon_name);
//             }

//             $talent->update($request->validated());
//             DB::commit();

//             Log::info('Talent updated successfully', ['talent_id' => $talent->id]);

//             return redirect()->route('talents.index')->withSuccess('Talent updated!');
//         } catch (Exception $e) {
//             DB::rollBack();
//             Log::error('Failed to update talent', ['talent_id' => $id, 'error' => $e->getMessage()]);

//             return redirect()->back()->withErrors($e->getMessage());
//         }
//     }

//     /**
//      * Delete a talent locally and on AYON.
//      *
//      * @param int $id
//      * @return RedirectResponse
//      *
//      * @throws Exception
//      */
//     public function destroy(int $id): RedirectResponse
//     {
//         DB::beginTransaction();

//         try {
//             $talent = Talent::findOrFail($id);

//             $this->ayonClient->deleteUser($talent->ayon_name);
//             $talent->delete();

//             DB::commit();

//             Log::info('Talent deleted successfully', ['talent_id' => $id]);

//             return redirect()->route('talents.index')->withSuccess('Talent deleted!');
//         } catch (Exception $e) {
//             DB::rollBack();
//             Log::error('Failed to delete talent', ['talent_id' => $id, 'error' => $e->getMessage()]);

//             return redirect()->back()->withErrors($e->getMessage());
//         }
//     }
// }
