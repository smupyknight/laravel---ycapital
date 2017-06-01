<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Setting;
use App\AlertEmail;

class SettingsController extends Controller
{
    /**
     * Show settings page
     * @return view 
     */
    public function getIndex()
    {
        $criminal_jurisdiction = Setting::where('field','criminal_jurisdiction')->first();
        $enable_alerts = Setting::where('field','enable_alerts')->first();
        $alert_emails = AlertEmail::get();
        return view('pages.admin.settings')
                    ->with('criminal_jurisdiction',$criminal_jurisdiction)
                    ->with('enable_alerts',$enable_alerts)
                    ->with('alert_emails',$alert_emails)
                    ->with('title','Admin Settings');
    }

    /**
     * Handle submit setting data
     * @param  Request $request 
     * @return redirect           
     */
    public function postIndex(Request $request)
    {
        if ($request->criminal_jurisdiction != '') {
            $this->saveCriminalJurisdiction($request);
        }

        if ($request->enable_alerts != '') {
            $this->saveEnableAlerts($request);
        }

        return redirect('/admin/settings');
    }

    public function postAddEmailAlert(Request $request)
    {
        $this->validate($request,[
                'email' => 'required|email|unique:alert_emails,email',
            ]);

        $email = new AlertEmail;
        $email->email = $request->email;
        $email->save();
    }

    public function postDeleteEmailAlert(Request $request,$id)
    {
        $email = AlertEmail::findOrFail($id);
        $email->delete();
    }

    private function saveEnableAlerts(Request $request)
    {
        $enable_alerts = Setting::where('field','enable_alerts')->first();

        if (! $enable_alerts) {
            $enable_alerts = new Setting;
            $enable_alerts->field = 'enable_alerts';
        }

        $enable_alerts->value = $request->enable_alerts;
        $enable_alerts->save();
    }

    /**
     * Save criminal jurisdiction setting
     * @param  Request $request 
     * @return null           
     */
    private function saveCriminalJurisdiction(Request $request)
    {
        $criminal_jurisdiction = Setting::where('field','criminal_jurisdiction')->first();

        if (! $criminal_jurisdiction) {
            $criminal_jurisdiction = new Setting;
            $criminal_jurisdiction->field = 'criminal_jurisdiction';
        }

        $criminal_jurisdiction->value = $request->criminal_jurisdiction;
        $criminal_jurisdiction->save();
    }
}
