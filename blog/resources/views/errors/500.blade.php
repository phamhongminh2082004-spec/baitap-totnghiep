{{-- TRANG LỖI 500: lỗi máy chủ (lỗi thật vẫn được ghi vào storage/logs/laravel.log). --}}
@extends('errors.layout')
@section('code', '500')
@section('title', 'Máy chủ gặp sự cố')
@section('message', 'Đã có lỗi xảy ra phía chúng tôi. Vui lòng thử lại sau ít phút.')
