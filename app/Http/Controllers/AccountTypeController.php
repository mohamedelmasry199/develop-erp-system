<?php

namespace App\Http\Controllers;

use App\AccountType;
use Illuminate\Http\Request;

class AccountTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if (!auth()->user()->can('account.access')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = session()->get('user.business_id');

        $account_types = AccountType::where('business_id', $business_id)
                                     ->whereNull('parent_account_type_id')
                                     ->get();

        return view('account_types.create')
                ->with(compact('account_types'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if (!auth()->user()->can('account.access')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $input = $request->only(['name', 'parent_account_type_id']);
            $input['business_id'] = $request->session()->get('user.business_id');

            AccountType::create($input);
            $output = ['success' => true,
                            'msg' => __("lang_v1.added_success")
                        ];
        } catch (\Exception $e) {
            \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());
            
            $output = ['success' => false,
                            'msg' => __("messages.something_went_wrong")
                        ];
        }

        return redirect()->back()->with('status', $output);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\AccountType  $accountType
     * @return \Illuminate\Http\Response
     */
    public function show(AccountType $accountType)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\AccountType  $accountType
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        if (!auth()->user()->can('account.access')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = session()->get('user.business_id');

        $account_type = AccountType::where('business_id', $business_id)
                                     ->findOrFail($id);

        $account_types = AccountType::where('business_id', $business_id)
                                     ->whereNull('parent_account_type_id')
                                     ->get();

        return view('account_types.edit')
                ->with(compact('account_types', 'account_type'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\AccountType  $accountType
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        if (!auth()->user()->can('account.access')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $input = $request->only(['name', 'parent_account_type_id']);
            $business_id = $request->session()->get('user.business_id');

            $account_type = AccountType::where('business_id', $business_id)
                                     ->findOrFail($id);

            //Account type is changed to subtype update all its sub type's parent type
            if (empty($account_type->parent_account_type_id) && !empty($input['parent_account_type_id'])) {
                AccountType::where('business_id', $business_id)
                        ->where('parent_account_type_id', $account_type->id)
                        ->update(['parent_account_type_id' => $input['parent_account_type_id']]);
            }

            $account_type->update($input);
                                    
            $output = ['success' => true,
                            'msg' => __("lang_v1.updated_success")
                        ];
        } catch (\Exception $e) {
            \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());
            
            $output = ['success' => false,
                            'msg' => __("messages.something_went_wrong")
                        ];
        }

        return redirect()->back()->with('status', $output);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\AccountType  $accountType
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if (!auth()->user()->can('account.access')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = session()->get('user.business_id');

        AccountType::where('business_id', $business_id)
                                     ->where('id', $id)
                                     ->delete();

        //Upadete parent account if set
        AccountType::where('business_id', $business_id)
                 ->where('parent_account_type_id', $id)
                 ->update(['parent_account_type_id' => null]);

        $output = ['success' => true,
                            'msg' => __("lang_v1.deleted_success")
                        ];

        return redirect()->back()->with('status', $output);
    }
}




  <?php
                                        function renderAccountTypes($account_types, $mainButtonAdded = array(), $level = 0)
                                        {
                                            foreach ($account_types as $account_type) {
                                                ?>
                                                <div class="MuiTreeItem-root Mui-expanded">
                                                    <div class="MuiTreeItem-content">
                                                        <?php
                                                        // Use an icon for folder based on the level
                                                        if ($level > 0) {
                                                            echo str_repeat('&nbsp;&nbsp;&nbsp;', $level - 1);
                                                        
                                                            if ($account_type->sub_types) {
                                                                echo '|-- ';
                                                            }
                                                        }
                                                        ?>
                                                        <span class="toggle-button closed"><i class="fas fa-folder"></i></span>
                                                        {{ $account_type->name }}
                                                    </div>
                                                    <div class="MuiCollapse-container MuiTreeItem-group">
                                                        {!! Form::open(['url' => action('AccountTypeController@destroy', $account_type->id), 'method' => 'delete']) !!}
                                                        <button type="button" class="btn btn-primary btn-modal btn-xs"
                                                            data-href="{{ action('AccountTypeController@edit', ['parent_id' => $account_type->id]) }}"
                                                            data-container="#account_type_modal">
                                                            <i class="fa fa-edit"></i> Edit</button>

                                                            <button type="submit" class="btn btn-danger btn-xs delete_account_type">
                                                                <i class="fa fa-trash"></i> Delete
                                                            </button>

                                                        <?php
                    if ($account_type->account_type == 'main' && !isset($mainButtonAdded[$account_type->id])) {
                        // Add a button for creating sub-child for main rows
                        ?>
                                                        <button type="button" class="btn btn-success btn-xs create_sub_child"
                                                            data-parent-id="{{ $account_type->id }}">
                                                            <a
                                                                href="{{ action('AccountTypeController@createSub', ['parent_id' => $account_type->id]) }}">
                                                                <i class="fa fa-plus"></i> Create Sub-Child
                                                            </a>
                                                        </button>

                                                        <?php
                        $mainButtonAdded[$account_type->id] = true;
                    }
                    ?>
                                                        {!! Form::close() !!}

                                                        <?php
                                                        if (!empty($account_type->sub_types)) {
                                                            // Recursive call for sub-types with increased level
                                                            renderAccountTypes($account_type->sub_types, $mainButtonAdded, $level + 1);
                                                        }
                                                        ?>
                                                    </div>
                                                </div>
                                                <?php
                                            }
                                        }
                                        ?>

                                                {{-- Call the recursive function with the top-level account types --}}
                                                @foreach ($account_types as $account_type)
                                                    <?php renderAccountTypes([$account_type]); ?>
                                                @endforeach

                                                <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
                                                <script>
                                                    // Add collapsible behavior using jQuery
                                                    $(document).ready(function() {
                                                        $('.MuiTreeItem-root .toggle-button').click(function() {
                                                            var toggleButton = $(this);
                                                            toggleButton.toggleClass('opened closed');
                                                            toggleButton.closest('.MuiTreeItem-root').find('.MuiCollapse-container').toggle();
                                                        });

                                                        // Initially close child rows
                                                        $('.MuiTreeItem-root .MuiCollapse-container').hide();
                                                    });
                                                </script>

                                                <script src="https://kit.fontawesome.com/YOUR_KIT_CODE.js" crossorigin="anonymous"></script>
