@extends('panel::layouts.app')
@section('body-class', 'design-app-home')
@section('title', __('FlexShipping::route.title'))

@push('header')
<script src="{{ asset('vendor/vue/3.5/vue.global.prod.js') }}"></script>
<script src="{{ asset('vendor/clipboard.min.js') }}"></script>
<script src="{{ asset('vendor/element-plus/index.full.js') }}"></script>
<script src="{{ asset('vendor/element-plus/icons.min.js') }}"></script>
<link rel="stylesheet" href="{{ plugin_asset('flex_shipping', 'css/flexshipp.css') }}">
@endpush

@section('content')
<div class="card h-min-600">
  <div id="app">

    <div class="card-header d-flex justify-content-between">
      <div class="mt-2 fs-5">{{ __('FlexShipping::route.freight') }}</div>
      <button @click="logSelectedItem" class="btn btn-primary">{{ __('FlexShipping::route.add') }}</button>
    </div>
    <div class="card-body">
      <div class="container">
        <div class="row" style="width: 70%;">
          <div v-for="(item, index) in items" :key="index" class="col-4 d-flex align-items-center">
            <div style="width: 80px;" class="me-2">@{{ item.state }}</div>
            <input v-if="item.state === '{{ __('FlexShipping::route.sort') }}'" type="number"
              v-model.number="item.frame" class="form-control w-100">
            <select
              v-if="['{{ __('FlexShipping::route.state') }}', '{{ __('FlexShipping::route.state_debugging') }}'].includes(item.state)"
              v-model.number="item.frame" class="form-control w-100">
              <option value="{{ __('FlexShipping::route.forbidden') }}">{{ __('FlexShipping::route.forbidden') }}
              </option>
              <option value="{{ __('FlexShipping::route.enable') }}">{{ __('FlexShipping::route.enable') }}</option>
            </select>
          </div>
        </div>
        <div class="d-flex gap-5" style="margin-top: 60px;">
          <div class="col-md-2">
            <div @click="setSelectedItem(index)" :class="{'selected': selectedIndex === index}" class="move"
              v-for="(item, index) in delivermove" :key="index">
              <div class="movediv">@{{ item.display_namecn|| item.name }}</div>
              <div>
                {{-- <i class="bi bi-trash" @click="showConfirmDelete(index)"></i> --}}
                <el-butto plain @click="open(index)">
                  <i class="bi bi-trash"></i>
                </el-butto>
              </div>
            </div>
            <div @click="addShopItem" class="d-flex gap-1 add-distribution mt-3">
              <i class="bi bi-plus-square"></i>
              <div>{{ __('FlexShipping::route.shipping_method') }}</div>
            </div>
          </div>

          <div class="col-md-10">
            <div class="d-flex">
              <div v-for="(item, index) in selectedItem.list" :key="index">
                <div @click="setSubNavigation(index)" :class="{'navigation-cut': selectedItem.subIndx === index}"
                  class="navigation">@{{ item.name }}
                </div>
              </div>
            </div>
            <div v-if="selectedItem.subIndx === 0">
              <div class="mt-3">
                <div class="mb-3">
                  <div class="d-flex gap-3">
                    <div class="general mt-2">{{ __('FlexShipping::route.display_name') }}</div>
                    <div class="row w-100">
                      <div class="col-6">
                        <div class="input-group">
                          <div class="input-group-prepend">
                            <span class="Informationid input-group-text" id="basic-addon1">中文</span>
                          </div>
                          <input v-model="selectedItem.display_namecn" type="text" class="Informationbox form-control"
                            placeholder="中文" aria-label="Username" aria-describedby="basic-addon1">
                        </div>
                        <div class="input-group">
                          <div class="input-group-prepend">
                            <span class="Informationid input-group-text" id="basic-addon2">English</span>
                          </div>
                          <input v-model="selectedItem.list[selectedItem.subIndx].from.display_nameen" type="text"
                            class="Informationbox form-control" placeholder="English" aria-label="Username"
                            aria-describedby="basic-addon2">
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="mb-3">
                  <div class="d-flex gap-3">
                    <div class="general mt-2">{{ __('FlexShipping::route.describe') }}</div>

                    <div class="row w-100">

                      <div class="col-6">
                        <div class="input-group">
                          <span class="Informationlong input-group-text">中文</span>
                          <textarea v-model="selectedItem.list[selectedItem.subIndx].from.description_cn"
                            placeholder="中文" class="Informationbox form-control" aria-label="With textarea"></textarea>
                        </div>

                        <div class="input-group">
                          <span class="Informationlong input-group-text">English</span>
                          <textarea v-model="selectedItem.list[selectedItem.subIndx].from.description_en"
                            placeholder="English" class="Informationbox form-control"
                            aria-label="With textarea"></textarea>

                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <div class="mb-3">
                  <div class="d-flex">
                    <div class="mt-2">{{ __('FlexShipping::route.icon') }}</div>
                    <div class="d-flex flex-column flex-grow-1"></div>
                  </div>
                </div>
                <div class="mb-3">
                  <div class="d-flex">
                    <div class="mt-2" style="width: 115px;">{{ __('FlexShipping::route.sort') }}</div>
                    <div class="row w-100">
                      <div class="col-6">
                        <input v-model="selectedItem.list[selectedItem.subIndx].from.sorting" type="number"
                          class="form-control" placeholder="{{ __('FlexShipping::route.sort') }}" aria-label="Username"
                          aria-describedby="basic-addon1">
                      </div>
                    </div>
                  </div>
                </div>
                <div class="mb-3">
                  <div class="d-flex">
                    <div class="mt-2" style="width: 115px;">{{ __('FlexShipping::route.tax_category') }}</div>
                    <div class="row w-100">
                      <div class="col-6">
                        <select v-model="selectedItem.list[selectedItem.subIndx].from.category_tax" class="form-select"
                          aria-label="Default select example">
                          <option value="0">--无--</option>
                          <option value="1">选项 1</option>
                        </select>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="mb-3">
                  <div class="d-flex">
                    <div class="mt-2" style="width: 115px;">{{ __('FlexShipping::route.state') }}</div>
                    <div class="row w-100">
                      <div class="col-6">
                        <select v-model="selectedItem.list[selectedItem.subIndx].from.state" class="form-select"
                          aria-label="Default select example">
                          <option value="0">{{ __('FlexShipping::route.forbidden') }}</option>
                          <option value="1">{{ __('FlexShipping::route.enable') }}</option>
                        </select>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div v-if="selectedItem.subIndx === 1">
              <div class="mt-3">
                <div class="col-12 col-md-6">
                  <div class="mb-3">
                    <div class="d-flex">
                      <div class="mt-2 ">{{ __('FlexShipping::route.group') }}</div>
                      <div style="flex-grow: 1; margin-left: 50px;">
                        <input v-model="selectedItem.list[selectedItem.subIndx].from.regional_group"
                          class="form-control" placeholder="{{ __('FlexShipping::route.group') }}" aria-label="Username"
                          aria-describedby="basic-addon1">
                        <div class="mt-2">{{ __('FlexShipping::route.turn') }}
                          <a href="{{panel_route('regions.index')}}" target="_blank" style="text-decoration: none;">{{
                            __('FlexShipping::route.configuration') }}</a>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="col-12 col-md-6">
                  <div class="mb-3">
                    <div class="d-flex">
                      <div class="mt-2">{{ __('FlexShipping::route.service') }}</div>
                      <div style="flex-grow: 1; margin-left: 65px;">
                        <select v-model="selectedItem.list[selectedItem.subIndx].from.service" class="form-select"
                          aria-label="Default select example">
                          <option value="">{{ __('FlexShipping::route.all_service') }}</option>
                          <option value="1">{{ __('FlexShipping::route.assign_service') }}</option>
                        </select>
                        <div v-if="selectedItem.list[selectedItem.subIndx].from.service==1" class="selection_screen">
                          <section class="selection_option">
                            <label class="checkbox-inline mb-1 me-1">
                              <input v-model="selectedItem.list[selectedItem.subIndx].from.customer_groups"
                                type="checkbox" :value="1"> {{ __('FlexShipping::route.silver') }}
                            </label>
                            <label class="checkbox-inline mb-1 me-1">
                              <input v-model="selectedItem.list[selectedItem.subIndx].from.customer_groups"
                                type="checkbox" :value="2"> {{ __('FlexShipping::route.gold') }}
                            </label>
                          </section>
                          <div v-if="selectedItem.list[selectedItem.subIndx].from.customer_groups.length < 1"
                            class="mt-3 text-danger">
                            {{ __('FlexShipping::route.client_select') }}
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="col-12 col-md-6">
                  <div class="mb-3 mt-5">
                    <div class="d-flex">
                      <div class="mt-2">{{ __('FlexShipping::route.currency') }}</div>
                      <div style="flex-grow: 1; margin-left: 80px;">

                        <select v-model="selectedItem.list[selectedItem.subIndx].from.currency" class="form-select"
                          aria-label="Default select example">
                          <option value="">{{ __('FlexShipping::route.all_currency') }}</option>
                          <option value="currencies">{{ __('FlexShipping::route.assign_currency') }}</option>
                        </select>
                        <div v-if="selectedItem.list[selectedItem.subIndx].from.currency === 'currencies'"
                          class="selection_screen">
                          <section class="selection_option">
                            <label class="checkbox-inline mb-1 me-1">
                              <input v-model="selectedItem.list[selectedItem.subIndx].from.currencies" type="checkbox"
                                :value="'CNY'">
                              {{ __('FlexShipping::route.rmb') }}
                            </label>
                            <label class="checkbox-inline mb-1 me-1">
                              <input v-model="selectedItem.list[selectedItem.subIndx].from.currencies" type="checkbox"
                                :value="'{{ __('FlexShipping::route.usd') }}'">
                              {{ __('FlexShipping::route.usd') }}
                            </label>
                            <label class="checkbox-inline mb-1 me-1">
                              <input v-model="selectedItem.list[selectedItem.subIndx].from.currencies" type="checkbox"
                                :value="'EUR'">
                              {{ __('FlexShipping::route.eur') }}
                            </label>
                          </section>
                          <div v-if="selectedItem.list[selectedItem.subIndx].from.currencies.length < 1"
                            class="mt-3 text-danger">
                            {{ __('FlexShipping::route.select_currency') }}
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div v-if="selectedItem.subIndx === 2">
              <div class="mt-3">
                <div class="mb-3">
                  <div class="d-flex gap-3" style="width: 100%;">
                    <div class="mt-2" style="width: 8%;">商品条件</div>
                    <div style="margin-left: 15px;" class="flex-grow-1">
                      <select v-model="selectedItem.list[selectedItem.subIndx].from.terms_commodity" class="form-select"
                        style="width: 45%;" aria-label="商品条件选择">
                        <option value="1">所有商品</option>
                        <option value="">购物车只能有以下选定的商品</option>
                        <option value="2">购物车有以下任一选定的商品即可</option>
                        <option value="3">购物车不能有以下选定的商品</option>
                      </select>
                      <div
                        v-if="selectedItem.list[selectedItem.subIndx].from.terms_commodity === '' || selectedItem.list[selectedItem.subIndx].from.terms_commodity === '2' || selectedItem.list[selectedItem.subIndx].from.terms_commodity === '3'">
                        <div class="mt-3">
                          <div class="input-group mb-3" style="width: 45%;">
                            <span class="input-group-text" id="basic-addon1">
                              <i class="bi bi-search"></i>
                            </span>
                            <input type="text" class="form-control" placeholder="搜索" data-bs-toggle="dropdown"
                              aria-label="搜索">
                            <div class="btn-group">
                              <ul class="dropdown-menu">
                                <li><a class="dropdown-item">111111111
                                  </a></li>
                                <li><a class="dropdown-item">Another action</a></li>
                                <li><a class="dropdown-item">Something else here</a></li>
                                <li>
                                  <hr class="dropdown-divider">
                                </li>
                                <li><a class="dropdown-item">Separated link</a></li>
                              </ul>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="mb-3">
                  <div class="d-flex gap-3" style="width: 100%;">
                    <div class="mt-2" style="width: 8%;">商品分类条件</div>
                    <div style="margin-left: 15px;" class="flex-grow-1">
                      <select v-model="selectedItem.list[selectedItem.subIndx].from.goods_category" class="form-select"
                        style="width: 45%;" aria-label="商品分类条件选择">
                        <option value="1">所有分类</option>
                        <option value="">购物车只能有以下选定的商品分类</option>
                        <option value="2">购物车有以下任一选定的商品分类即可</option>
                        <option value="3">购物车不能有以下选定的分类商品</option>
                      </select>
                    </div>

                  </div>
                </div>
                <div class="mb-3">
                  <div class="d-flex gap-3" style="width: 100%;">
                    <div class="mt-2" style="width: 8%;">品牌条件</div>
                    <div style="margin-left: 15px;" class="flex-grow-1">
                      <select v-model="selectedItem.list[selectedItem.subIndx].from.brand_conditions"
                        class="form-select" style="width: 45%;" aria-label="品牌条件选择">
                        <option value="">所有品牌</option>
                        <option value="1">购物车只能有以下选定的品牌</option>
                        <option value="2">购物车有以下任一选定的品牌即可</option>
                        <option value="3">购物车不能有以下选定的</option>
                      </select>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div v-if="selectedItem.subIndx === 3">
              <div>
                <div class="mt-3">
                  <div class="mb-3">
                    <div class="d-flex gap-3">
                      <div class="mt-2">{{ __('FlexShipping::route.week') }}</div>
                      <div style="padding: 12px;">
                        <section>
                          <label style="margin-left: 10px" class="checkbox-inline mb-0 me-0">
                            <input v-model="selectedItem.list[selectedItem.subIndx].from.date" type="checkbox"
                              :value="1">
                            {{ __('FlexShipping::route.monday') }}
                          </label>
                          <label style="margin-left: 10px" class="checkbox-inline mb-0 me-0">
                            <input v-model="selectedItem.list[selectedItem.subIndx].from.date" type="checkbox"
                              :value="2">
                            {{ __('FlexShipping::route.tuesday') }}
                          </label>
                          <label style="margin-left: 10px" class="checkbox-inline mb-0 me-0">
                            <input v-model="selectedItem.list[selectedItem.subIndx].from.date" type="checkbox"
                              :value="3">
                            {{ __('FlexShipping::route.wednesday') }}
                          </label>
                          <label style="margin-left: 10px" class="checkbox-inline mb-0 me-0">
                            <input v-model="selectedItem.list[selectedItem.subIndx].from.date" type="checkbox"
                              :value="4">
                            {{ __('FlexShipping::route.thursday') }}
                          </label>
                          <label style="margin-left: 10px" class="checkbox-inline mb-0 me-0">
                            <input v-model="selectedItem.list[selectedItem.subIndx].from.date" type="checkbox"
                              :value="5">
                            {{ __('FlexShipping::route.friday') }}
                          </label>
                          <label style="margin-left: 10px" class="checkbox-inline mb-0 me-0">
                            <input v-model="selectedItem.list[selectedItem.subIndx].from.date" type="checkbox"
                              :value="6">
                            {{ __('FlexShipping::route.saturday') }}
                          </label>
                          <label style="margin-left: 10px" class="checkbox-inline mb-0 me-0">
                            <input v-model="selectedItem.list[selectedItem.subIndx].from.date" type="checkbox"
                              :value="7">
                            {{ __('FlexShipping::route.sunday_full') }}
                          </label>
                        </section>
                      </div>
                    </div>
                  </div>
                  <div class="mb-3">
                    <div class="d-flex gap-3">
                      <div class="mt-2" style="width: 80px;">{{ __('FlexShipping::route.time_period') }}</div>
                      <div class="d-flex gap-3" style="flex: 0 0 50%;">
                        <div style="flex: 1;">
                          <select class="form-control" v-model="selectedItem.list[selectedItem.subIndx].from.afternoon">
                            <option value="">{{ __('FlexShipping::route.any') }}</option>
                            @for ($i = 0; $i <= 23; $i++) <option value="{{ $i < 10 ? ('0' . $i) : $i }}:00">
                              {{ $i < 10 ? ('0' . $i) : $i }}:00 @if ($i < 12) {{ __('FlexShipping::route.forenoon') }}
                                @else {{ __('FlexShipping::route.afternoon') }} @endif </option>
                                @endfor
                          </select>
                        </div>
                        <div style="flex: 1;">
                          <select class="form-control" v-model="selectedItem.list[selectedItem.subIndx].from.morning">
                            <option value="">{{ __('FlexShipping::route.any') }}</option>
                            @for ($i = 0; $i <= 23; $i++) <option value="{{ $i < 10 ? ('0' . $i) : $i }}:00">
                              {{ $i < 10 ? ('0' . $i) : $i }}:00 @if ($i < 12) {{ __('FlexShipping::route.forenoon') }}
                                @else {{ __('FlexShipping::route.afternoon') }} @endif </option>
                                @endfor
                          </select>
                        </div>

                      </div>
                    </div>
                    <div class="container d-flex justify-content-start mt-2">
                      {{ __('FlexShipping::route.current_server_time') }}
                    </div>
                  </div>
                </div>
              </div>

            </div>
            <div v-if="selectedItem.subIndx === 4">
              <div class="mt-3">
                <div class="mb-3">
                  <div class="d-flex gap-3 align-items-center">
                    <div class="mt-2" style="width: 100px;">{{ __('FlexShipping::route.billing_unit') }}</div>
                    <div class="col-6">
                      <select v-model="selectedItem.list[selectedItem.subIndx].from.billing" class="form-select mt-3 "
                        aria-label="{{ __('FlexShipping::route.billing_unit') }}">
                        <option value="0">{{ __('FlexShipping::route.fixed_fee') }}</option>
                        <option value="1">{{ __('FlexShipping::route.total_amount') }}</option>
                        <option value="2">{{ __('FlexShipping::route.total_weight') }}</option>
                        <option value="3">{{ __('FlexShipping::route.total_quantity') }}</option>
                      </select>
                    </div>
                  </div>
                  <div class="mt-3 mb-3 d-flex gap-3 align-items-center"
                    v-if="selectedItem.list[selectedItem.subIndx].from.billing==0">
                    <div style="width: 100px;">{{ __('FlexShipping::route.fixed_fee') }}</div>
                    <div class="col-6">
                      <div class="input-group mb-3 mt-3">
                        <span class="input-group-text" id="basic-addon1">{{ __('FlexShipping::route.usd') }}</span>
                        <input min="0" v-model="selectedItem.list[selectedItem.subIndx].from.billing_usd" type="number"
                          class="form-control" placeholder="{{ __('FlexShipping::route.enter_amount') }}"
                          aria-label="{{ __('FlexShipping::route.enter_amount') }}" aria-describedby="basic-addon1">
                      </div>
                    </div>
                  </div>
                  <div class="mt-3 mb-3 gap-3"
                    v-if="selectedItem.list[selectedItem.subIndx].from.billing === '1'||selectedItem.list[selectedItem.subIndx].from.billing === '2'||selectedItem.list[selectedItem.subIndx].from.billing === '3'">
                    <div>
                      <div class="d-flex align-items-center">
                        <div class="mt-2" style="width: 100px;">{{ __('FlexShipping::route.billing_method') }}</div>
                        <div class="col-6">
                          <select v-model="selectedItem.list[selectedItem.subIndx].from.method_billing"
                            class="form-select ms-3" style="margin-left: 17px;">
                            <option value="0">{{ __('FlexShipping::route.range_billing')}}</option>
                            <option value="1">{{ __('FlexShipping::route.cumulative_billing')}}
                            </option>
                          </select>
                        </div>
                      </div>
                    </div>
                    <div>
                      <div class="d-flex">
                        <div class="mt-3" style="width: 110px;">{{ __('FlexShipping::route.billing_method') }}</div>
                        <table class="table table-bordered caption-top mt-3"
                          style="margin-left: 15px; margin-right: 50px;">
                          <thead>
                            <tr class="bs-secondary-bg-rgb">
                              <th scope="col">{{ __('FlexShipping::route.start_value') }}</th>
                              <th scope="col">{{ __('FlexShipping::route.end_value')}}</th>
                              <th scope="col">{{ __('FlexShipping::route.fee')}}</th>
                              <th scope="col">{{ __('FlexShipping::route.incremental_quantity')}}</th>
                              <th></th>
                            </tr>
                          </thead>
                          <tbody>
                            <tr v-for="(item, index) in selectedItem.list[selectedItem.subIndx].from.items"
                              :key="index">
                              <td>
                                <div class="input-group">
                                  <span class="input-group-text" id="basic-addon1"> >= </span>
                                  <input min=0 v-model="item.start_value" type="number" class="form-control"
                                    aria-label="{{ __('FlexShipping::route.start_value') }}"
                                    aria-describedby="{{ __('FlexShipping::route.start_value') }}">
                                </div>
                              </td>
                              <td>
                                <div class="input-group">
                                  <span class="input-group-text" id="basic-addon1">
                                    <= </span>
                                      <input min=0 v-model="item.end_value" type="number" class="form-control"
                                        aria-label="结束值(含)" aria-describedby="结束值(含)">
                                </div>
                              </td>
                              <td>
                                <div class="input-group">
                                  <span class="input-group-text" id="basic-addon1">{{ __('FlexShipping::route.usd')
                                    }}</span>
                                  <input min=0 v-model="item.fee" type="number" class="form-control" aria-label="费用"
                                    aria-describedby="费用">
                                </div>
                              </td>
                              <td>
                                <div class="input-group">
                                  <input min=0 v-model="item.progressive_quantity" type="number" class="form-control"
                                    aria-label="累进数量" aria-describedby="累进数量">
                                </div>
                              </td>
                              <td>
                                <div class="mt-2 d-flex justify-content-center" style="width: 130px;">
                                  <div class="btn btn-danger" @click="deleteItem(index)">{{
                                    __('FlexShipping::route.delete')}}</div>
                                </div>
                              </td>
                            </tr>
                            <tr>
                              <td class="bordernone" style="border-left: 1px solid #dee2e6"></td>
                              <td class="bordernone"></td>
                              <td class="bordernone"></td>
                              <td class="bordernone"></td>
                              <td class="mt-2 d-flex justify-content-center">
                                <div class="btn btn-primary" @click="add_rules">{{
                                  __('FlexShipping::route.add_fee_rule')}}</div>
                              </td>
                            </tr>
                          </tbody>
                        </table>

                      </div>
                      <div>
                        <div class=" mb-3 d-flex gap-3 align-items-center">
                          <div style="width: 100px;">{{ __('FlexShipping::route.add_fee_rule')}}</div>
                          <div class="col-6">
                            <div class="input-group mb-3 mt-3">
                              <span class="input-group-text" id="basic-addon1">{{ __('FlexShipping::route.usd')
                                }}</span>
                              <input v-model="selectedItem.list[selectedItem.subIndx].from.extra" class="form-control"
                                placeholder="{{ __('FlexShipping::route.enter_amount') }}"
                                aria-label="{{ __('FlexShipping::route.enter_amount') }}"
                                aria-describedby="basic-addon1">
                            </div>
                          </div>
                        </div>
                      </div>
                      <div>
                        <div class=" mb-3 d-flex gap-3 align-items-center">
                          <div style="width: 100px;">{{ __('FlexShipping::route.maximum_fee') }}</div>
                          <div class="col-6">
                            <div class="input-group mb-3 mt-3">
                              <span class="input-group-text" id="basic-addon1">{{ __('FlexShipping::route.usd')
                                }}</span>
                              <input v-model="selectedItem.list[selectedItem.subIndx].from.upper_limit"
                                class="form-control" placeholder="{{ __('FlexShipping::route.enter_amount') }}"
                                aria-label="{{ __('FlexShipping::route.enter_amount') }}"
                                aria-describedby="basic-addon2">
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      {{-- <div v-if="showModal" class="modal-overlay">
        <div class="modal-content">
          <div class="d-flex justify-content-between align-items-center modal-lete">
            <div>信息</div>
            <div><i @click="cancelDelete" class="bi bi-x-lg"></i></div>
          </div>
          <div class="fs-5 my-3" style="width: 200px;">
            <div>要删除该商品吗?</div>
          </div>
          <div class="d-flex gap-3 modal-main">
            <div class="modal-confirm" @click="confirmDelete">确定删除</div>
            <div class="modal-cancel" @click="cancelDelete">取消</div>
          </div>
        </div>
      </div> --}}
    </div>
  </div>
</div>
@endsection

@push('footer')
<script>
  const { ref, watch, createApp } = Vue;
     const { ElMessage, ElMessageBox } = ElementPlus;

     const app = createApp({
      setup() {
      const items = ref([
        {state: '{{ __('FlexShipping::route.sort') }}', frame: '1'},
        {state: '{{ __('FlexShipping::route.state') }}', frame: '{{ __('FlexShipping::route.forbidden') }}'},
        {state: '{{ __('FlexShipping::route.state_debugging') }}', frame: '{{ __('FlexShipping::route.forbidden') }}'}
      ]);
      const delivermove = ref([]);
      const selectedIndex = ref(null);
      const selectedItem = ref({name: '', list: []});
      // const showModal = ref(false);
      const currentIndex = ref(null);

      watch(
        () => selectedItem.value.list[selectedItem.value.subIndx]?.from,
        (newFrom, oldFrom) => {
          const newCurrency = newFrom?.currency;
          const oldCurrency = oldFrom?.currency;
          if (newCurrency === '') {
            selectedItem.value.list[selectedItem.value.subIndx].from.currencies = [];
          } else if (newCurrency === 'currencies' && oldCurrency !== 'currencies') {
            selectedItem.value.list[selectedItem.value.subIndx].from.currencies = [];
          }

          const newService = newFrom?.service;
          if (newService === '') {
            selectedItem.value.list[selectedItem.value.subIndx].from.customer_groups = [];
          } else if (newService === '1') {
            selectedItem.value.list[selectedItem.value.subIndx].from.customer_groups = [];
          }
        },
        {immediate: true}
      );

      watch(
        () => {
          const currentItem = selectedItem.value.list[selectedItem.value.subIndx];
          return currentItem ? currentItem.from?.billing : undefined;
        },
        (newBilling, oldBilling) => {
          const currentItem = selectedItem.value.list[selectedItem.value.subIndx];

          if (!currentItem || !currentItem.from) {
            return;
          }

          if (newBilling === '0') {
            currentItem.from.billing_usd = '';
            currentItem.from.extra = '';
            currentItem.from.upper_limit = '';
            currentItem.from.items = [];
            currentItem.from.method_billing = '';
          } else {
            currentItem.from.billing_usd = '';
            currentItem.from.extra = '';
            currentItem.from.upper_limit = '';
            currentItem.from.items = [];
            currentItem.from.method_billing = '';
          }
        },
        {immediate: true}
      );

      const addShopItem = () => {
        const newItem = {
          name: '{{ __('FlexShipping::route.unnamed') }}',
          idx: delivermove.value.length + 1,
          subIndx: 0,
          display_namecn: '',
          list: [
            {
              name: '{{ __('FlexShipping::route.essential_information') }}',
              from: {
                display_namecn: '',
                display_nameen: '',
                description_en: '',
                description_cn: '',
                ocons: '',
                sorting: '',
                category_tax: '0',
                state: '0'
              }
            },
            {
              name: '{{ __('FlexShipping::route.common_rules') }}',
              from: {regional_group: '', service: '', customer_groups: [], currency: '', currencies: []}
            },
            {name: '{{ __('FlexShipping::route.rules_commodity') }}', from: {terms_commodity: '', goods_category: '', brand_conditions: ''}},
            {name: '{{ __('FlexShipping::route.rules_time') }}', from: {date: [], morning: '', afternoon: ''}},
            {
              name: '{{ __('FlexShipping::route.rules_fees') }}',
              from: {billing: '3', billing_usd: null, method_billing: '', items: [], extra: '', upper_limit: ''}
            }
          ]
        };
        newItem.list[0].from.display_namecn = newItem.display_namecn;
        delivermove.value.push(newItem);
        selectedItem.value = newItem;
        selectedIndex.value = delivermove.value.length - 1;
      };

      const setSelectedItem = (index) => {
        selectedItem.value = delivermove.value[index];
        selectedIndex.value = index;
      };

      const setSubNavigation = (index) => {
        selectedItem.value.subIndx = index;
      };

      // const showConfirmDelete = (index) => {
      //   currentIndex.value = index;
      //   showModal.value = true;
      // };

      // const confirmDelete = () => {
      //   if (currentIndex.value !== null) {
      //     delivermove.value.splice(currentIndex.value, 1);
      //     if (selectedIndex.value === currentIndex.value) {
      //       if (delivermove.value.length > 0) {
      //         selectedIndex.value = Math.min(selectedIndex.value, delivermove.value.length - 1);
      //         selectedItem.value = delivermove.value[selectedIndex.value];
      //       } else {
      //         selectedIndex.value = null;
      //         selectedItem.value = {name: '', list: []};
      //       }
      //     } else if (selectedIndex.value > currentIndex.value) {
      //       selectedIndex.value = Math.max(selectedIndex.value - 1, 0);
      //     }
      //   }
      //   // closeModal();
      // };

      // const cancelDelete = () => {
      //   closeModal();
      // };

      // const closeModal = () => {
      //   showModal.value = false;
      //   currentIndex.value = null;
      // };

      const add_rules = () => {
        selectedItem.value.list[selectedItem.value.subIndx].from.items.push({
          start_value: '',
          end_value: '',
          fee: '',
          progressive_quantity: ''
        });
      };

      const deleteItem = (index) => {
        selectedItem.value.list[selectedItem.value.subIndx].from.items.splice(index, 1);
      };

      const logSelectedItem = () => {
        const filteredItems = delivermove.value.map(item => {
          const {name, subIndx, idx,display_namecn, ...rest} = item;
         return {
          ...rest,
          list: item.list.map((listItem, listIndex) => {
          const { name: listItemName, ...listRest } = listItem;
          if (listIndex === 0) {
          listRest.display_namecn = item.display_namecn || listItem.from.display_namecn;
          }
          
          return listRest;
          })
          };
        });
        const itemsWithoutState = items.value.map(item => {
          const {state, ...rest} = item;
          return rest;
        });
        console.log(itemsWithoutState);
        console.log(filteredItems);

        axios.put('{{ panel_route('flex_shipping.update') }}', {
          items: itemsWithoutState,
          filteredItems: filteredItems
        })
          .then(response => {
            console.log('服务器返回的数据:', response.data);
          })
          .catch(error => {
            console.error('发送请求失败:', error);
          });
      };
            const open = (index) => {
            currentIndex.value = index;
            
            ElMessageBox.confirm(
            '确认要删除商品吗？',
            '信息',
            {
            distinguishCancelAndClose: true,
            confirmButtonText: '取消',
            cancelButtonText: '确定删除',
            }
            )
            .then(() => {
            })
            .catch(() => {
            // 点击了确定删除按钮时执行删除操作
            if (currentIndex.value !== null) {
            // 删除对应的项
            delivermove.value.splice(currentIndex.value, 1);
            // 可选：在删除后进行一些后续处理
            if (delivermove.value.length > 0) {
            // 如果还有剩余项，选择新的项
            selectedIndex.value = Math.min(selectedIndex.value, delivermove.value.length - 1);
            selectedItem.value = delivermove.value[selectedIndex.value];
            } else {
            // 如果没有剩余项，清空选中项
            selectedIndex.value = null;
            selectedItem.value = { name: '', list: [] };
            }
            
            }
            });
            };
     

      return {
        items,
        delivermove,
        selectedItem,
        selectedIndex,
        addShopItem,
        // showConfirmDelete,
        // confirmDelete,
        // cancelDelete,
        // showModal,
        currentIndex,
        setSelectedItem,
        setSubNavigation,
        logSelectedItem,
        deleteItem,
        add_rules,
        open
      };
    }
  });

  app.use(ElementPlus); 
  app.mount('#app');
</script>
@endpush