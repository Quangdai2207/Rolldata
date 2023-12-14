<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules\Exists;
use Nette\Utils\Random;

class RollData extends Controller
{
    public function index()
    {
        $context = stream_context_create(
            array(
                "http" => array(
                    "header" => "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/119.0.0.0 Safari/537.36"
                )
            )
        );

        $html = file_get_html("https://www.yachtcharterfleet.com/sailing-yacht-charter-fleet.htm", false, $context);
        
        $yachts = []; // insert yacths;
        
        $info_detail = []; // insert info_detail yachts;
        
        $data = $html->find(".searchResultsContinued");
        
        foreach ($data as $value) {
            $images = $value->find(".jsYachtSearchResult img");
            $titles = $value->find(".jsYachtSearchResult .details h4 a.js_compare_name span");
            $prices = $value->find(".jsYachtSearchResult .price-container p .price span.actual-price");
            $slots = $value->find(".jsYachtSearchResult .details h4 span.guests span");
            $cabins = $value->find(".jsYachtSearchResult .details h4 span.cabins");
            $length = $value->find(".jsYachtSearchResult p.builder span.js_compare_length");
            $company_build = $value->find(".jsYachtSearchResult .details p.builder span.js_compare_builder");
            $year_build = $value->find(".jsYachtSearchResult .details p.length");
            
            // Yachts info
            foreach ($images as $key => $item) {
                $id = "Y".strval(rand(0, 1000)).chr(rand(97, 122));
                $images = $item->src;
                if (!empty($images)) {
                    $yachts[$key]['id'] = $id;
                    $yachts[$key]['image'] = $images;
                    // $file_name = basename($images);
                    // $img_path = public_path('/upload/'.  $file_name);
                    // $this->download_file($yachts[$key]['image'], $img_path);
                }
            }

            foreach ($titles as $key => $item) {
                $name_yacht = $item->innertext;
                if (!empty($name_yacht)) {
                    $yachts[$key]['name'] = $name_yacht;
                }
            }

            foreach ($prices as $key => $item) {
                $price = $item->innertext;
                if (!empty($price)) {
                    $yachts[$key]['price'] = $this->formatPrice($price);
                }
            }

            // Yachts detail Info
            foreach ($slots as $key => $item) {
                $id = "Y".strval(rand(0, 1000)).chr(rand(97, 122));
                $slot = $item->innertext;
                $info_detail[$key]['id'] = $id;
                $info_detail[$key]['crew'] = $slot;
            }

            foreach ($cabins as $key => $item) {
                $cabin = $item->innertext;
                $info_detail[$key]['cabin'] = $cabin;
            }

            foreach ($length as $key => $item) {
                $length_yacth = $item->innertext;
                $info_detail[$key]['length'] = $this->to_slug($length_yacth);
            }

            foreach ($company_build as $key => $item) {
                $company_build = $item->innertext;
                $info_detail[$key]['company_build'] = $this->to_slug($company_build);
            }

            foreach ($year_build as $key => $item) {
                $year_build = $item->innertext;
                $info_detail[$key]['yearBuild'] = $this->to_slug($year_build);
            }
        }
        dd($yachts);
        dd($info_detail);
        echo(count($yachts));
        // DB::table('yachts')->insert($yachts);
        // $yacht_id = DB::table('yachts')->select('id')->get();
        
        // DB::table('yacht_specifications')->where('yatch_id', $yacht_id)->insert($info_detail);
    }

    public function download_file($file_url, $filename)
    {
        file_put_contents($filename, file_get_contents($file_url));
    }

    public function formatPrice($price)
    {
        $price = str_replace("<!-- --> - <!-- -->", " - ", $price);
        $price = str_replace("\u{A0}₫", " VND", $price);
        return $price;
    }

    public function to_slug($str)
    {
        $str = trim(mb_strtolower($str));
        $str = preg_replace('/(à|á|ạ|ả|ã|â|ầ|ấ|ậ|ẩ|ẫ|ă|ằ|ắ|ặ|ẳ|ẵ)/', 'a', $str);
        $str = preg_replace('/(è|é|ẹ|ẻ|ẽ|ê|ề|ế|ệ|ể|ễ)/', 'e', $str);
        $str = preg_replace('/(ì|í|ị|ỉ|ĩ)/', 'i', $str);
        $str = preg_replace('/(ò|ó|ọ|ỏ|õ|ô|ồ|ố|ộ|ổ|ỗ|ơ|ờ|ớ|ợ|ở|ỡ)/', 'o', $str);
        $str = preg_replace('/(ù|ú|ụ|ủ|ũ|ư|ừ|ứ|ự|ử|ữ)/', 'u', $str);
        $str = preg_replace('/(ỳ|ý|ỵ|ỷ|ỹ)/', 'y', $str);
        $str = preg_replace('/(đ)/', 'd', $str);
        $str = preg_replace('/[^a-z0-9-\s]/', '-', $str);
        $str = preg_replace('/([\s]+)/', '-', $str);
        $str = preg_replace('/-+/', '-', $str);
        return $str;
    }
}
