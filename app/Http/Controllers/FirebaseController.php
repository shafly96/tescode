<?php
 
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Kreait\Firebase;
use Kreait\Firebase\Factory;
use Kreait\Firebase\ServiceAccount;
use Kreait\Firebase\Database;
use Lcobucci\JWT\Builder;
use Response;
 
class FirebaseController extends Controller{

    public function koneksi(){

        $serviceAccount = ServiceAccount::fromJsonFile(__DIR__.'/laravelfirebase-4a323-firebase-adminsdk-qru6a-97d431cd8c.json');

        $firebase = (new Factory) 
        ->withServiceAccount($serviceAccount)
        ->withDatabaseUri('https://laravelfirebase-4a323.firebaseio.com/')
        ->create();

        return $firebase;
    }

    public function getDB(){
        $database = $this->koneksi()->getDatabase();

        return $database;
    }
 
    public function register(Request $request){
        $database = $this->getDB();
        
        $id = bin2hex($request->email);
        $cek = $database->getReference('user/'.$id)->getSnapshot()->exists();
        
        if(!$cek){
            try{ 
                $newPost = $database
                ->getReference('user/'.$id)
                ->set([
                    'email' => $request->email,
                    'password' => encrypt($request->password)
                ]);
            }catch(\Exception $e){
                return Response::json(['status' => '0', 'message' => 'Gagal register']);
            }
        }else{
            return Response::json(['status' => '0', 'message' => 'Email sudah terdaftar']);
        }

        return Response::json(['status' => '1', 'message' => 'Berhasil register']);
    }

    public function login(Request $request){
        $database = $this->getDB();
        
        $id = bin2hex($request->email);
        $cek = $database->getReference('user/'.$id)->getSnapshot();

        if($cek->exists()){
            $pass = $cek->getValue();

            if($request->password == decrypt($pass['password'])){
                $token = (new Builder())->setIssuer('shafly')
                        ->setId('4f1g23a12aa', true)
                        ->setIssuedAt(time())
                        ->setNotBefore(time())
                        ->setExpiration(time() + 3600)
                        ->set('uid', 1)
                        ->getToken();

                return Response::json(['status' => '1', 'message' => 'Login berhasil', 'token' => (string) $token]);
            }
            else return Response::json(['status' => '0', 'message' => 'Password salah']);
        }else{
            return Response::json(['status' => '0', 'message' => 'Email tidak terdaftar']);
        }
    }

    public function sudahLogin(Request $request){
        return Response::json(['status' => '1', 'message' => date('Y-m-d H:i:s').' - Session masih berlaku']);
    }
 
}