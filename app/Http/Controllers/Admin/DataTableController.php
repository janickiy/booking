<?php

namespace App\Http\Controllers\Admin;

use App\Repositories\OfficesRepository;
use DataTables;
use App\Models\SessionLog;
use App\Models\Admin\{AdminUser, AdminRole};
use App\Models\{User,
    Settings,
    OrdersRailway,
    Pages,
    TManager,
    Languages,
    Orders,
    OrderMessages,
    Role,
    OrdersLog,
    OrdersAeroexpress,
    OrdersAvia,
    OrdersBus};
use App\Models\References\{
    RailwayStation, TrainsCar, Trains
};
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Http\Controllers\TranslationManager\Manager;
use App\Helpers\StringHelpers;

use App\Models\Admin\Hotel\{AdminHotel,
    HotelRegion,
    HotelsAttributes,
    HotelOrders,
    HotelsAttributesProviders};

use URL;
use Form;

class DataTableController extends Controller
{
    protected $manager;

    protected $input;

    /**
     * DataTableController constructor.
     * @param Manager $manager
     */
    public function __construct(Manager $manager)
    {
        $this->manager = $manager;
        parent::__construct();
    }

    /***
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function action(Request $request)
    {
        if (isset($request->action)) {
            switch ($request->action) {

                case 'get_hotel_regions':

                    $type = $request->type ? $request->type : null;
                    $id = $request->id ? $request->id : null;

                    $json = [];

                    if ($type) {
                        $arrs = HotelRegion::where('name_ru', 'like', '%' . $request->q . '%')
                            ->where('type', 'city')
                            ->orderBy('name_ru')
                            ->get();
                    } else {
                        $arrs = HotelRegion::where('name_ru', 'like', '%' . $request->q . '%')
                            ->orderBy('name_ru')
                            ->get();
                    }

                    foreach ($arrs as $row) {
                        if ($row->parent_id != 0)
                            $name = $row->name_ru . ' (' . $row->parent->name_ru . ')';
                        else
                            $name = $row->name_ru;

                        $json[] = ['id' => $row->id, 'text' => $name];
                    }

                    return response()->json($json);

                    break;

                case 'get_content_slug':

                    $slug = \App\Helpers\StringHelpers::slug(trim($request->title));
                    $count = Pages::where('slug', 'LIKE%', $slug)->count();
                    $slug = $count > 0 ? $slug . ($count + 1) : $slug;

                    return response()->json(['slug' => $slug]);

                    break;

                case 'clean_tmanager':

                    $this->manager->cleanTranslations();

                    return response()->json(['result' => true, 'msg' => 'Пустые значение удалены']);

                    break;

                case 'import_tmanager':

                    $counter = $this->manager->importTranslations();

                    return response()->json(['result' => true, 'msg' => 'Иморт заверщен. Импортировано ' . $counter . ' значений']);

                    break;

                case 'reset_tmanager':

                    $this->manager->truncateTranslations();

                    return response()->json(['result' => true, 'msg' => 'Все переводы удалены']);

                    break;

                case 'cache_clean':

                    $this->manager->cleanCache();

                    return response()->json(['result' => true, 'msg' => 'Кэш очищен']);

                    break;

                case 'hide_language':

                    $languages = Languages::find($request->id);

                    $hide = $languages->hide ? 0 : 1;

                    $languages->hide = $hide;
                    $languages->save();

                    break;

                case 'get_order_items':

                    $id = $request->id;

                    $order = Orders::find($id);

                    $html = '<table class="table table-striped table-bordered table-hover">';
                    $html .= '<thead>';
                    $html .= '<tr>';
                    $html .= '<th data-hide="phone">Номер заказа</th>';
                    $html .= '<th data-hide="phone">Размещен</th>';
                    $html .= '<th data-hide="phone">Тип</th>';
                    $html .= '<th data-hide="phone,tablet">Статус</th>';
                    $html .= '<th data-hide="phone,tablet">Стоимость заказа</th>';
                    $html .= '</tr>';
                    $html .= '</thead>';
                    $html .= '<tbody>';

                    if ($order) {
                        foreach ($order->orderItems as $row) {
                            if (isset($row->type)) {
                                switch ($row->type) {

                                    case "railway":

                                        if (isset($order->railway)) {
                                            foreach ($order->railway as $item) {
                                                $html .= '<tr>';
                                                $html .= '<td><a href="' . URL::route('admin.ordersrailway.info', ['id' => $item->orderId]) . '#' . $id . '">' . $row->id . '</a></td>';
                                                $html .= '<td>' . $item->created_at . '</td>';
                                                $html .= '<td>' . Orders::$type_name[$row->type] . '</td>';
                                                $html .= '<td>' . OrdersRailway::$status_name[$item->orderStatus] . '</td>';
                                                $html .= '<td>' . $item->Amount . '</td>';
                                                $html .= '</tr>';
                                            }
                                        }

                                        break;

                                    case "aeroexpress":

                                        if (isset($order->aeroexpress)) {
                                            foreach ($order->aeroexpress as $item) {
                                                $html .= '<tr>';
                                                $html .= '<td><a href="' . URL::route('admin.orderaeroexpress.info', ['id' => $item->orderId]) . '#' . $id . '">' . $row->id . '</a></td>';
                                                $html .= '<td>' . $item->created_at . '</td>';
                                                $html .= '<td>' . Orders::$type_name[$row->type] . '</td>';
                                                $html .= '<td>' . OrdersAeroexpress::$status_name[$item->orderStatus] . '</td>';
                                                $html .= '<td>' . $item->Amount . '</td>';
                                                $html .= '</tr>';
                                            }
                                        }

                                        break;

                                    case "avia":

                                        if (isset($order->avia)) {
                                            foreach ($order->avia as $item) {
                                                $html .= '<tr>';
                                                $html .= '<td><a href="' . URL::route('admin.avia.info', ['id' => $item->orderId]) . '#' . $id . '">' . $row->id . '</a></td>';
                                                $html .= '<td>' . $item->created_at . '</td>';
                                                $html .= '<td>' . Orders::$type_name[$row->type] . '</td>';
                                                $html .= '<td>' . OrdersAvia::$status_name[$item->orderStatus] . '</td>';
                                                $html .= '<td>' . $item->Amount . '</td>';
                                                $html .= '</tr>';
                                            }
                                        }

                                        break;

                                    case "bus":

                                        if (isset($order->bus)) {
                                            foreach ($order->bus as $item) {
                                                $html .= '<tr>';
                                                $html .= '<td><a href="' . URL::route('admin.bus.info', ['id' => $item->orderId]) . '#' . $id . '">' . $row->id . '</a></td>';
                                                $html .= '<td>' . $item->created_at . '</td>';
                                                $html .= '<td>' . Orders::$type_name[$row->type] . '</td>';
                                                $html .= '<td>' . OrdersBus::$status_name[$item->orderStatus] . '</td>';
                                                $html .= '<td>' . $item->Amount . '</td>';
                                                $html .= '</tr>';
                                            }
                                        }


                                        break;

                                }
                            }
                        }
                    }

                    $html .= '</tbody>';
                    $html .= '</table>';

                    echo $html;

                    break;
            }
        }
    }

    /**
     * @param Request $request
     * @return mixed
     * @throws \Exception
     */
    public function getSessionLog(Request $request)
    {
        $dates = explode(' - ', $request->date);
        $user = $request->user;

        if ($user) {
            $result = User::where('email', 'like', "$user%");
            $userList = $result->pluck('userId');
        }

        if (array_key_exists(0, $dates) && array_key_exists(1, $dates)) {
            $start = Carbon::parse($dates[0])->format('Y-m-d H:i:s');
            $end = Carbon::parse($dates[1])->format('Y-m-d H:i:s');

            if (isset($userList) && $userList) {
                $logs = SessionLog::whereBetween('created_at', [$start, $end])->whereIn('user_id', $userList->toArray());
            } else {
                $logs = SessionLog::whereBetween('created_at', [$start, $end]);
            }
        } else

            if (isset($userList) && $userList) {
                $logs = SessionLog::whereIn('user_id', $userList->toArray());
            } else {
                $logs = SessionLog::query();
            }

        return Datatables::of($logs)
            ->editColumn('session_log_id', function ($logs) {
                return '<a href="' . URL::route('admin.logs.info', ['id' => $logs->session_log_id]) . '">' . $logs->session_log_id . '</a>';
            })
            ->editColumn('session_id', function ($logs) {
                return '<a href="#" class="choose_session_id" data-content="' . $logs->session_id . '">' . $logs->session_id . '</a>';
            })
            ->addColumn('user_id', function ($logs) {
                $user = User::where('userId', $logs->user_id)->first();

                return isset($user->login) && $user->login ? $user->login : '-';

            })->rawColumns(['user_id', 'session_log_id', 'session_id'])->make(true);
    }

    /**
     * @return mixed
     * @throws \Exception
     */
    public function getAdminUsers()
    {
        $row = AdminUser::query();

        return Datatables::of($row)
            ->addColumn('actions', function ($row) {
                $editBtn = '<a title="Редактировать" class="btn btn-xs btn-primary"  href="' . URL::route('admin.users.edit', ['id' => $row->adminUserId]) . '"><span  class="fa fa-edit"></span></a> &nbsp;';

                if ($row->adminUserId != \Auth::id())
                    $deleteBtn = '<a class="btn btn-xs btn-danger deleteRow" id="' . $row->adminUserId . '"><span class="fa fa-remove"></span></a>';
                else
                    $deleteBtn = '';

                return $editBtn . $deleteBtn;

            })
            ->addColumn('role', function ($row) {

                $adminRoles = AdminRole::join('admin_user_roles', 'admin_user_roles.adminRoleId', '=', 'admin_roles.adminRoleId')
                    ->where('admin_user_roles.adminUserId', $row->adminUserId)
                    ->pluck('admin_roles.name');

                $names = $adminRoles->toArray();

                return $adminRoles ? implode(",", $names) : '';
            })
            ->rawColumns(['actions'])->make(true);
    }

    /**
     * @return mixed
     * @throws \Exception
     */
    public function getRole()
    {
        $row = AdminRole::query();

        return Datatables::of($row)
            ->addColumn('actions', function ($row) {
                $editBtn = '<a title="Редактировать" class="btn btn-xs btn-primary"  href="' . URL::route('admin.role.edit', ['id' => $row->adminRoleId]) . '"><span  class="fa fa-edit"></span></a> &nbsp;';
                $deleteBtn = '<a class="btn btn-xs btn-danger deleteRow" id="' . $row->adminRoleId . '"><span class="fa fa-remove"></span></a>';

                if (!in_array($row->adminRoleId, [1]))
                    return $editBtn . $deleteBtn;
                else
                    return '';

            })->rawColumns(['actions'])->make(true);
    }

    /**
     * @return mixed
     * @throws \Exception
     */
    public function getSettings()
    {
        \Cache::flush();

        $row = Settings::query();

        return Datatables::of($row)
            ->addColumn('actions', function ($row) {
                $editBtn = '<a title="Редактировать" class="btn btn-xs btn-primary"  href="' . URL::route('admin.settings.edit', ['id' => $row->settingId]) . '"><span  class="fa fa-edit"></span></a> &nbsp;';
                $deleteBtn = '<a class="btn btn-xs btn-danger deleteRow" id="' . $row->settingId . '"><span class="fa fa-remove"></span></a>';

                return $editBtn . $deleteBtn;

            })->rawColumns(['actions'])->make(true);
    }

    /**
     * @return mixed
     * @throws \Exception
     */
    public function getPortalUsers()
    {
        $row = User::orderBy('userId');

        return Datatables::of($row)
            ->addColumn('actions', function ($row) {

                $editBtn = '<a title="Редактировать" class="btn btn-xs btn-primary"  href="' . URL::route('admin.portalusers.edit', ['id' => $row->userId]) . '"><span  class="fa fa-edit"></span></a> &nbsp;';
                $deleteBtn = '<a class="btn btn-xs btn-danger deleteRow" id="' . $row->userId . '"><span class="fa fa-remove"></span></a>';

                return $editBtn . $deleteBtn;
            })
            ->editColumn('contacts', function ($row) {
                if (isset($row->contacts)) {
                    $contacts = StringHelpers::ObjectToArray($row->contacts);
                    $lastname = $contacts['lastName'] ?? '';
                    $firstname = $contacts['firstName'] ?? '';
                    $middlename = $contacts['middleName'] ?? '';

                    return $firstname && $lastname ? $lastname . ' ' . $firstname . ' ' . $middlename : $row->email;

                } else
                    return '';
            })
            ->rawColumns(['actions'])->make(true);
    }

    /**
     * @return mixed
     * @throws \Exception
     */
    public function getOrdersRailways()
    {
        $row = OrdersRailway::query();

        return Datatables::of($row)
            ->addColumn('checkbox', function ($row) {
                return '<input type="checkbox" title="Отметить/Снять отметку" value="' . $row->orderId . '" name="activate[]">';
            })
            ->editColumn('orderId', function ($row) {
                return '<a href="' . URL::route('admin.ordersrailway.info', ['id' => $row->orderId]) . '">' . $row->orderId . '</a>';
            })
            ->editColumn('orderStatus', function ($row) {
                return OrdersRailway::$status_name[$row->orderStatus];
            })
            ->addColumn('user', function ($row) {
                return isset($row->user->login) && $row->user->login ? $row->user->login : '-';
            })
            ->rawColumns(['orderId', 'checkbox'])->make(true);
    }

    /**
     * @return mixed
     * @throws \Exception
     */
    public function getTrainsCar()
    {
        $row = TrainsCar::with('trains')->get();

        return Datatables::of($row)
            ->editColumn('trains.trainName', function ($row) {
                return isset($trainsCar->trains->trainName) ? $row->trains->trainName : $row->trainName;
            })
            ->editColumn('trains.trainNumber', function ($row) {
                return isset($row->trains->trainNumber) ? $row->trains->trainNumber : 'ВСЕ';
            })
            ->editColumn('schemes', function ($row) {
                return isset($row->schemes) ? "да (" . count($row->schemes) . ")" : 'нет';
            })
            ->addColumn('actions', function ($row) {
                $editBtn = '<a title="Редактировать" class="btn btn-xs btn-primary"  href="' . URL::route('admin.trainscar.edit', ['id' => $row->id]) . '"><span  class="fa fa-edit"></span></a> &nbsp;';
                $deleteBtn = '<a class="btn btn-xs btn-danger deleteRow" id="' . $row->id . '"><span class="fa fa-remove"></span></a>';

                return $editBtn . $deleteBtn;

            })
            ->rawColumns(['actions'])->make(true);
    }

    /**
     * @return mixed
     */
    public function getRailwayStation()
    {
        $row = RailwayStation::where('railway_station.code', '!=', '')->with(['city', 'country']);

        return Datatables::of($row)
            ->editColumn('city.nameRu', function ($row) {
                return $row->city->nameRu ?? '';
            })
            ->editColumn('country.nameRu', function ($row) {
                return $row->country->nameRu ?? '';
            })
            ->editColumn('info.popularity', function ($row) {
                return $row->info->popularity ?? '';
            })
            ->addColumn('actions', function ($row) {
                $editBtn = '<a title="Редактировать" class="btn btn-xs btn-primary"  href="' . URL::route('admin.stations.edit', ['id' => $row->railwayStationId]) . '"><span  class="fa fa-edit"></span></a>';
                return $editBtn;
            })
            ->rawColumns(['actions'])
            ->make(true);
    }

    /**
     * @return mixed
     * @throws \Exception
     */
    public function getTrains()
    {
        $row = Trains::query();

        return Datatables::of($row)
            ->addColumn('actions', function ($row) {
                $editBtn = '<a title="Редактировать" class="btn btn-xs btn-primary"  href="' . URL::route('admin.trains.edit', ['id' => $row->id]) . '"><span  class="fa fa-edit"></span></a> &nbsp;';
                $deleteBtn = '<a class="btn btn-xs btn-danger deleteRow" id="' . $row->id . '"><span class="fa fa-remove"></span></a>';

                return $editBtn . $deleteBtn;

            })
            ->rawColumns(['actions'])->make(true);
    }

    /**
     * @return mixed
     * @throws \Exception
     */
    public function getPages()
    {
        $row = Pages::query()->with('parent');

        return Datatables::of($row)
            ->editColumn('title', function ($row) {
                $title = StringHelpers::ObjectToArray($row->title);

                return $title['ru'] ?? '';
            })
            ->editColumn('pages.parent', function ($row) {
                return $row->parent->title->ru ?? '';
            })
            ->editColumn('page_path', function ($row) {
                return $row->PagePathType ?? '';
            })
            ->addColumn('actions', function ($row) {
                $editBtn = '<a title="Редактировать" class="btn btn-xs btn-primary"  href="' . URL::route('admin.pages.edit', ['id' => $row->id]) . '"><span  class="fa fa-edit"></span></a> &nbsp;';
                $deleteBtn = '<a class="btn btn-xs btn-danger deleteRow" id="' . $row->id . '"><span class="fa fa-remove"></span></a>';

                return $editBtn . $deleteBtn;
            })
            ->rawColumns(['actions'])->make(true);
    }

    /**
     * @return mixed
     * @throws \Exception
     */
    public function getTmanager()
    {
        $row = TManager::query();

        return Datatables::of($row)
            ->addColumn('actions', function ($row) {
                $editBtn = '<a title="Редактировать" class="btn btn-xs btn-primary"  href="' . URL::route('admin.tmanager.edit', ['id' => $row->id]) . '"><span  class="fa fa-edit"></span></a> &nbsp;';
                $deleteBtn = '<a class="btn btn-xs btn-danger deleteRow" id="' . $row->id . '"><span class="fa fa-remove"></span></a>';

                return $editBtn . $deleteBtn;
            })
            ->rawColumns(['actions'])->make(true);
    }

    /**
     * @return mixed
     * @throws \Exception
     */
    public function getLanguages()
    {
        $row = Languages::orderBy('id');

        return Datatables::of($row)
            ->editColumn('hide', function ($row) {
                return Form::checkbox('hide', 1, $row->hide == 1 ? true : false, ['class' => 'hideRow', 'id' => $row->id]);
            })
            ->rawColumns(['hide'])->make(true);
    }

    /**
     * @return mixed
     * @throws \Exception
     */
    public function getOrders()
    {
        $row = Orders::query()->with('user');

        return Datatables::of($row)

            ->editColumn('id', function ($row) {
                return '<a class="orderRow" href="#' . $row->id . '" id="' . $row->id . '">' . $row->id . '</a>';
            })

            ->editColumn('log', function ($row) {
                return '<a class="orderRow" href="' . URL::route('admin.orders_log.list', ['orderId' => $row->id]) . '">смотреть</a>';
            })

            ->editColumn('user.contacts', function ($row) {

                if (isset($row->user->contacts)) {
                    $contacts = StringHelpers::ObjectToArray($row->user->contacts);
                    $lastname = $contacts['lastName'] ?? '';
                    $firstname = $contacts['firstName'] ?? '';
                    $middlename = $contacts['middleName'] ?? '';

                    return $firstname && $lastname ? $firstname . ' ' . $firstname . ' ' . $middlename : $row->user->email;

                } else
                    return '';
            })
            ->rawColumns(['id','log'])->make(true);
    }

    /**
     * @return mixed
     * @throws \ErrorException
     */
    public function getOffices()
    {
        $typesRepo = OfficesRepository::getInstance();
        $offices = $typesRepo->getAll();

        return Datatables::of($offices)
            ->editColumn('name', function ($offices) {
                return $offices->name['ru'] ?? '';
            })
            ->editColumn('address', function ($offices) {
                return $offices->address['ru'] ?? '';
            })
            ->editColumn('city', function ($offices) {
                return $offices->city['ru'] ?? '';
            })
            ->addColumn('actions', function ($offices) {
                $editBtn = '<a title="Редактировать" class="btn btn-xs btn-primary"  href="' . URL::route('admin.offices.edit', ['id' => $offices->id]) . '"><span  class="fa fa-edit"></span></a> &nbsp;';
                $deleteBtn = '<a class="btn btn-xs btn-danger deleteRow" id="' . $offices->id . '"><span class="fa fa-remove"></span></a>';

                return $editBtn . $deleteBtn;

            })
            ->rawColumns(['actions'])->make(true);
    }

    /**
     * @return mixed
     * @throws \Exception
     */
    public function getOrderMessages()
    {
        $row = OrderMessages::selectRaw('DISTINCT ON (order_messages.order_id) order_messages.*')->whereIn('order_messages.status', [0, 1, 2])->orderBy('order_messages.order_id')->orderBy('order_messages.id')->with('user');

        return Datatables::of($row)
            ->editColumn('message', function ($row) {

                return '<a href="' . URL::route('admin.order_messages.messages', ['order_id' => $row->order_id, 'receiver_id' => $row->receiver_id]) . '">' . StringHelpers::shortText($row->message, 500) . '</a>';
            })
            ->editColumn('user.contacts', function ($row) {
                $contacts = StringHelpers::ObjectToArray($row->user->contacts);
                $lastname = $contacts['lastName'] ?? '';
                $firstname = $contacts['firstName'] ?? '';
                $middlename = $contacts['middleName'] ?? '';

                return $firstname && $lastname ? $firstname . ' ' . $firstname . ' ' . $middlename : $contacts['contactEmails'];
            })
            ->addColumn('actions', function ($row) {
                $deleteBtn = '<a class="btn btn-xs btn-danger deleteRow" id="' . $row->id . '"><span class="fa fa-remove"></span></a>';

                return $deleteBtn;
            })
            ->rawColumns(['actions', 'message'])->make(true);
    }

    /**
     * @param $receiver_id
     * @return mixed
     */
    public function getMessages($receiver_id)
    {
        $row = OrderMessages::whereRaw('(receiver_id=' . $receiver_id . ' AND status IN (0,1,3)) OR (sender_id=' . $receiver_id . ' AND status IN (0,1,2))')->with('user');

        return Datatables::of($row)
            ->editColumn('user.contacts', function ($row) {
                $contacts = StringHelpers::ObjectToArray($row->user->contacts);
                $lastname = $contacts['lastName'] ?? '';
                $firstname = $contacts['firstName'] ?? '';
                $middlename = $contacts['middleName'] ?? '';

                return $firstname && $lastname ? $firstname . ' ' . $firstname . ' ' . $middlename : $contacts['contactEmails'];
            })
            ->make(true);

    }

    /**
     * @return mixed
     */
    public function getPortalUsersRole()
    {
        $row = Role::query();

        return Datatables::of($row)
            ->addColumn('actions', function ($row) {
                $editBtn = '<a title="Редактировать" class="btn btn-xs btn-primary"  href="' . URL::route('admin.portal_users_role.edit', ['id' => $row->roleId]) . '"><span  class="fa fa-edit"></span></a> &nbsp;';
                $deleteBtn = '<a class="btn btn-xs btn-danger deleteRow" id="' . $row->roleId . '"><span class="fa fa-remove"></span></a>';

                return $editBtn . $deleteBtn;

            })->rawColumns(['actions'])->make(true);
    }

    /**
     * @return mixed
     */
    public function getHotel()
    {
        $row = AdminHotel::query()->with('regions');

        return Datatables::of($row)
            ->editColumn('hotels_hotels.regions', function ($row) {
                return $row->regions->name_ru ?? '';
            })
            ->addColumn('actions', function ($row) {
                $editBtn = '<a title="Редактировать" class="btn btn-xs btn-primary"  href="' . URL::route('admin.hotel.edit', ['id' => $row->id]) . '"><span  class="fa fa-edit"></span></a> &nbsp;';
                $deleteBtn = '<a class="btn btn-xs btn-danger deleteRow" id="' . $row->id . '"><span class="fa fa-remove"></span></a>';

                return $editBtn . $deleteBtn;

            })->rawColumns(['actions'])->make(true);
    }

    /**
     * @return mixed
     */
    public function getHotelsAttributes()
    {
        $row = HotelsAttributes::query();

        return Datatables::of($row)
            ->editColumn('name_ru', function ($row) {
                return '<a href="' . URL::route('admin.hotels_attributes_providers.list', ['attribute_id' => $row->id]) . '">' . $row->name_ru . '</a>';
            })
            ->addColumn('actions', function ($row) {
                $editBtn = '<a title="Редактировать" class="btn btn-xs btn-primary"  href="' . URL::route('admin.hotels_attributes.edit', ['id' => $row->id]) . '"><span  class="fa fa-edit"></span></a> &nbsp;';
                $deleteBtn = '<a class="btn btn-xs btn-danger deleteRow" id="' . $row->id . '"><span class="fa fa-remove"></span></a>';

                return $editBtn . $deleteBtn;

            })->rawColumns(['actions', 'name_ru'])->make(true);

    }

    /**
     * @return mixed
     */
    public function getHotelsAttributesProviders($attribute_id)
    {
        $row = HotelsAttributesProviders::where('attribute_id', $attribute_id);

        return Datatables::of($row)
            ->addColumn('actions', function ($row) {
                $editBtn = '<a title="Редактировать" class="btn btn-xs btn-primary"  href="' . URL::route('admin.hotels_attributes_providers.edit', ['attribute_id' => $row->attribute_id, 'type' => $row->type, 'code' => $row->code]) . '"><span  class="fa fa-edit"></span></a> &nbsp;';
                $deleteBtn = '<a class="btn btn-xs btn-danger deleteRow" id="' . $row->attribute_id . '" data-type="' . $row->type . '" data-code="' . $row->code . '"><span class="fa fa-remove"></span></a>';

                return $editBtn . $deleteBtn;

            })->rawColumns(['actions'])->make(true);
    }

    /**
     * @param int $parent_id
     * @return mixed
     */
    public function getHotelsRegions($parent_id = 0)
    {
        $row = HotelRegion::where('parent_id', $parent_id)->with('parent');

        return Datatables::of($row)
            ->editColumn('hotels_regions.parent', function ($row) {
                return $row->parent->name_ru ?? '';
            })
            ->editColumn('name_ru', function ($row) {
                return '<a href="' . URL::route('admin.hotels_regions.list', ['id' => $row->id]) . '">' . $row->name_ru . '</a>';
            })
            ->editColumn('is_sng', function ($row) {
                return $row->is_sng ? 'да' : 'нет';
            })
            ->addColumn('actions', function ($row) {
                $editBtn = '<a title="Редактировать" class="btn btn-xs btn-primary"  href="' . URL::route('admin.hotels_regions.edit', ['id' => $row->id]) . '"><span  class="fa fa-edit"></span></a> &nbsp;';
                $deleteBtn = '<a class="btn btn-xs btn-danger deleteRow" id="' . $row->id . '"><span class="fa fa-remove"></span></a>';

                return $editBtn . $deleteBtn;

            })->rawColumns(['actions', 'name_ru'])->make(true);
    }

    /**
     * @return mixed
     * admin.orders_hotels.offers
     */
    public function getOrdersHotel()
    {
        $row = HotelOrders::query();

        return Datatables::of($row)

            ->editColumn('id', function ($row) {
                return '<a href="' . URL::route('admin.orders_hotels.guests', ['id' => $row->id]) . '">' . $row->id . '</a>';
            })

            ->editColumn('offer_id', function ($row) {
                return '<a href="' . URL::route('admin.orders_hotels.offers', ['id' => $row->offer_id]) . '">' . $row->offer_id . '</a>';
            })

            ->rawColumns(['id', 'offer_id'])->make(true);
    }

    /**
     * @return mixed
     */
    public function getOrdersLog(Request $request)
    {
        $dates = explode(' - ', $request->date);

        if (array_key_exists(0, $dates) && array_key_exists(1, $dates)) {
            $start = Carbon::parse($dates[0])->format('Y-m-d H:i:s');
            $end = Carbon::parse($dates[1])->format('Y-m-d H:i:s');

            if (isset($userList) && $userList) {
                $row = OrdersLog::whereBetween('created_at', [$start, $end])->whereIn('user_id', $userList->toArray());
            } else {
                $row = OrdersLog::whereBetween('created_at', [$start, $end]);
            }
        } else
            if (isset($userList) && $userList) {
                $row = OrdersLog::whereIn('user_id', $userList->toArray());
            } else {
                $row = OrdersLog::query();
            }

        return Datatables::of($row)
            ->editColumn('orders_log_id', function ($row) {
                return '<a href="' . URL::route('admin.orders_log.info', ['id' => $row->orders_log_id]) . '">' . $row->orders_log_id . '</a>';
            })
            ->editColumn('error', function ($row) {
                return $row->error === true ? 'да' : 'нет';
            })
            ->rawColumns(['orders_log_id', 'error'])->make(true);
    }
}
