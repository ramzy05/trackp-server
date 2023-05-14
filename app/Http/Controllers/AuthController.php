<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;


class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'firstname' => 'required|string|max:255',
            'lastname' => 'required|string|max:255',
            // 'username' => 'required|string|max:255|unique:users',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'role' => 'string|in:agent,admin',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $username = strtolower($request->firstname[0] . $request->lastname); // Première lettre du prénom et nom de famille
        $suffix = '';
        while (User::where('username', $username . $suffix)->exists()) {
            $suffix = '_' . Str::random(4); // Ajouter une chaîne aléatoire de 4 caractères comme suffixe
        }
        $username .= $suffix; // Ajouter le suffixe unique
        $user = User::create([
            'firstname' => $request->firstname,
            'lastname' => $request->lastname,
            'username' => $username,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'role' => $request->role ? $request->role : 'agent',
            'avatar' => generateAvatar(),
            'is_online' => false,
        ]);


        return response()->json(['message' => "Account created, your username is: ". $username, 'user'=> $user], 200);
    }

    public function login(Request $request)
    {
        $credentials = $request->only('email', 'username', 'password');

        if (isset($credentials['email'])) {
            $loginKey = 'email';
        } elseif (isset($credentials['username'])) {
            $loginKey = 'username';
        } else {
            return response()->json(['error' => 'Invalid Credentials'], 401);
        }

        if (Auth::attempt([$loginKey => $credentials[$loginKey], 'password' => $credentials['password']])) {
            $user = Auth::user();
            $user->is_online = true;
            $user->save();

            $authToken= $user->createToken('basic-token',['create','read']);

            if ($user->role=="admin") {
                $authToken = $user->createToken('admin-token',['create','update','delete']);
                $user->authToken =  $authToken->plainTextToken;
                return response()->json([
                    'user' => $user,
                ], 200);
            }
            $user->authToken =  $authToken->plainTextToken;
            return response()->json([
                'user' => $user,
            ], 200);
        }

        return response()->json(['error' => 'Invalid Credentials'], 401);
    }

    public function logout(Request $request)
    {
        $user = $request->user();
        $user->is_online = false;
        $user->save();
        $request->user()->tokens()->delete();

        return response()->json(['message' => 'Successfully logged out'], 200);
    }
}


function generateAvatar(){
    $avatarStyle = ['Circle', 'Transparent'];
    $topType = ['NoHair','Eyepatch','Hat','Hijab','Turban','WinterHat1','WinterHat2','WinterHat3','WinterHat4','LongHairBigHair','LongHairBob','LongHairBun','LongHairCurly','LongHairCurvy','LongHairDreads','LongHairFrida','LongHairFro','LongHairFroBand','LongHairNotTooLong','LongHairShavedSides','LongHairMiaWallace','LongHairStraight','LongHairStraight2','LongHairStraightStrand','ShortHairDreads01','ShortHairDreads02','ShortHairFrizzle','ShortHairShaggyMullet','ShortHairShortCurly','ShortHairShortFlat','ShortHairShortRound','ShortHairShortWaved','ShortHairSides','ShortHairTheCaesar','ShortHairTheCaesarSidePart'];
    $accessoriesType = ['Blank','Kurt','Prescription01','Prescription02','Round','Sunglasses','Wayfarers'];
    $hairColor = ['Auburn','Black','Blonde','BlondeGolden','Brown','BrownDark','PastelPink','Blue','Platinum','Red','SilverGray'];
    $facialHairType = ['Blank','BeardMedium','BeardLight','BeardMajestic','MoustacheFancy','MoustacheMagnum'];
    $clotheType = ['BlazerShirt','BlazerSweater','CollarSweater','GraphicShirt','Hoodie','Overall','ShirtCrewNeck','ShirtScoopNeck','ShirtVNeck'];
    $clotheColor = ['Black','Blue01','Blue02','Blue03','Gray01','Gray02','Heather','PastelBlue','PastelGreen','PastelOrange','PastelRed','PastelYellow','Pink','Red','White'];
    $eyeType = ['Close','Cry','Default','Dizzy','EyeRoll','Happy','Hearts','Side','Squint','Surprised','Wink','WinkWacky'];
    $eyebrowType = ['Angry','AngryNatural','Default','DefaultNatural','FlatNatural','RaisedExcited','RaisedExcitedNatural','SadConcerned','SadConcernedNatural','UnibrowNatural','UpDown','UpDownNatural'];
    $mouthType = ['Concerned','Default','Disbelief','Eating','Grimace','Sad','ScreamOpen','Serious','Smile','Tongue','Twinkle','Vomit'];
    $skinColor = ['Tanned','Yellow','Pale','Light','Brown','DarkBrown','Black'];

    $url = "https://avataaars.io?";
    $url .= "avatarStyle=" . urlencode($avatarStyle[array_rand($avatarStyle)]);
    $url .= "&topType=" . urlencode($topType[array_rand($topType)]);
    $url .= "&accessoriesType=" . urlencode($accessoriesType[array_rand($accessoriesType)]);
    $url .= "&hairColor=" . urlencode($hairColor[array_rand($hairColor)]);
    $url .= "&facialHairType=" . urlencode($facialHairType[array_rand($facialHairType)]);
    $url .= "&clotheType=" . urlencode($clotheType[array_rand($clotheType)]);
    $url .= "&clotheColor=" . urlencode($clotheColor[array_rand($clotheColor)]);
    $url .= "&eyeType=" . urlencode($eyeType[array_rand($eyeType)]);
    $url .= "&eyebrowType=" . urlencode($eyebrowType[array_rand($eyebrowType)]);
    $url .= "&mouthType=" . urlencode($mouthType[array_rand($mouthType)]);
    $url .= "&skinColor=" . urlencode($skinColor[array_rand($skinColor)]);

    return $url;
}
