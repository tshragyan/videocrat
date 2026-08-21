<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Video;
use App\Services\InstagramVideoImporter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class VideoController extends Controller
{
    public function importFromInstagram(Request $request)
    {
        $url = "https://video.xx.fbcdn.net/o1/v/t2/f2/m86/AQMLE9FhHSbe3GNRM4ntAYOl-pbVTCLEtB-ZhQZYWUqYYrgbbUhpVpOpYEeFEY6C7NRVmfXx2kjf3VsfIEVGIUaM6vrMykfCqFLTuBc.mp4?_nc_cat=102&_nc_oc=AdogbXOQoioC8YeheIXWhVYDlZkRmVMtsAz-Wespzz8QTTc6HlOjUgoo2V-lJiEknv0&_nc_sid=5e9851&_nc_ht=instagram.fevn1-1.fna.fbcdn.net&_nc_ohc=3aEJcMNBY_cQ7kNvwFtz7Et&efg=eyJ2ZW5jb2RlX3RhZyI6Inhwdl9wcm9ncmVzc2l2ZS5JTlNUQUdSQU0uQ0xJUFMuQzMuNzIwLmRhc2hfYmFzZWxpbmVfMV92MSIsInhwdl9hc3NldF9pZCI6ODk5NjI1MDQ5ODcxODgyLCJhc3NldF9hZ2VfZGF5cyI6MjcsInZpX3VzZWNhc2VfaWQiOjEwMDk5LCJkdXJhdGlvbl9zIjozMiwidXJsZ2VuX3NvdXJjZSI6Ind3dyJ9&ccb=17-1&vs=65264fe177fab3b2&_nc_vs=HBksFQIYUmlnX3hwdl9yZWVsc19wZXJtYW5lbnRfc3JfcHJvZC81OTQ2OEE4RTg1NjhGMkFCOTFDQUE3RThBQTFEM0RBMF92aWRlb19kYXNoaW5pdC5tcDQVAALIARIAFQIYUWlnX3hwdl9wbGFjZW1lbnRfcGVybWFuZW50X3YyLzgwNDdDQjZFNDVCODFDMDAxNTM3QTMyQUUyM0I4RThGX2F1ZGlvX2Rhc2hpbml0Lm1wNBUCAsgBEgAoABgAGwKIB3VzZV9vaWwBMRJwcm9ncmVzc2l2ZV9yZWNpcGUBMRUAACaU2Lr4iI2ZAxUCKAJDMywXQEB64UeuFHsYEmRhc2hfYmFzZWxpbmVfMV92MREAdf4HZeadAQA&_nc_gid=jDO2ZE7b0lW4JuDRr3wh8Q&_nc_ss=7a22e&_nc_zt=28&oh=00_AQGfshtYZHEtfKJcmtFThI7GpW1ML7j0gP_fcmefcOJEsw&oe=6A8A1753";
        $response = Http::head($url);

        /** @var User $user */
        $user = auth()->user();
        $service = new InstagramVideoImporter();
        $data = $service->import(
            $url,
            $user->getDomain(),
            $user->shopify_token,
        );

        Video::query()->create([
          "title" => "title",
          "external_id" => $data['shopify_file_id'],
          "source" => Video::SOURCE_INSTAGRAM,
          "video_data" => json_encode($data),
          "user_id" => 1,
        ]);

        return response("success");
    }
}
