import 'dart:convert';
import 'package:shared_preferences/shared_preferences.dart';

/// Utility penyimpanan token/user untuk layer UI lain di luar AuthService
/// Menjembatani perbedaan key yang dipakai AuthService (`token`, `user`)
/// dan util lama (`auth_token`, `user_data`).
class AuthStorage {
  // Key lama
  static const String _legacyTokenKey = 'auth_token';
  static const String _legacyUserKey = 'user_data';

  // Key baru (dipakai AuthService)
  static const String _tokenKey = 'token';
  static const String _userKey = 'user';

  static Future<void> saveToken(String token) async {
    final prefs = await SharedPreferences.getInstance();
    // Simpan ke kedua key agar kompatibel
    await prefs.setString(_tokenKey, token);
    await prefs.setString(_legacyTokenKey, token);
  }

  static Future<String?> getToken() async {
    final prefs = await SharedPreferences.getInstance();
    // Coba baca dari key baru dulu, fallback ke key lama
    return prefs.getString(_tokenKey) ?? prefs.getString(_legacyTokenKey);
  }

  static Future<void> saveUser(Map<String, dynamic> user) async {
    final prefs = await SharedPreferences.getInstance();
    final jsonStr = jsonEncode(user);
    await prefs.setString(_userKey, jsonStr);
    await prefs.setString(_legacyUserKey, jsonStr);
  }

  static Future<void> clearAll() async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.remove(_tokenKey);
    await prefs.remove(_legacyTokenKey);
    await prefs.remove(_userKey);
    await prefs.remove(_legacyUserKey);
  }
}
