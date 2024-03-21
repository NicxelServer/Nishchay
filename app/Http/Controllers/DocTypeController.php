<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\EncryptionDecryptionHelper;
use App\Helpers\AuditLogHelper;
use Illuminate\Support\Facades\Date;
use App\Models\DocumentType;

class DocTypeController extends Controller
{
    //
    public function showDocType()
    {
        $docTypes = DocumentType::all();

        foreach($docTypes as $docType)
        {
            $docType->encDocTypeID = EncryptionDecryptionHelper::encdecId($technology->tbl_tech_id,'encrypt'); 
        }

        return view('frontend_admin.showDoc',['docTypes'=>$docTypes]);
    }

    //create doc type
    public function createDocType(Request $request)
    {
        $userDetails = session('user');

        $docType = new DocumentType;
        $docType->doc_type = $request->docType;
        $docType->flag = 'show';
        $docType->add_by = $userDetails->tbl_user_id;
        $docType->add_date = Date::now()->toDateString();
        $docType->add_time = Date::now()->toTimeString();
        $docType->save();

        AuditLogHelper::logDetails('created document type', $userDetails->tbl_user_id);


        return redirect()->back();
    }

    //delete doc type

    public function deleteDocType(Request $request)
    {
        $userDetails = session('user');

        $enc_doc_type_id = $request->input('enc_doc_type_id');
        $dec_doc_type_id = EncryptionDecryptionHelper::encdecId($enc_doc_type_id,'decrypt');

        $docType = DocumentType::where('tbl_doc_type_id',$dec_doc_type_id)->first();
        $docType->flag = 'deleted';
        $docType->deleted_by = $userDetails->tbl_user_id;
        $docType->deleted_date = Date::now()->toDateString();
        $docType->deleted_time = Date::now()->toTimeString();
        $docType->save();

        AuditLogHelper::logDetails('deleted document type', $userDetails->tbl_user_id);

        return redirect()->back();

    }
}
