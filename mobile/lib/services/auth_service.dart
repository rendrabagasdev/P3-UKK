import 'dart:convert';

import 'package:flutter/foundation.dart' show kIsWeb;
import 'package:flutter/material.dart';
import 'package:flutter_secure_storage/flutter_secure_storage.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:http/http.dart' as http;

import '../config/api_config.dart';
import '../models/user.dart';

class AuthService with ChangeNotifier {
  final storage = const FlutterSecureStorage();
  User? _currentUser;
  String? _token;
  bool _isLoading = false;

  User? get currentUser => _currentUser;
  String? get token => _token;
  bool get isLoading => _isLoading;
  bool get isAuthenticated => _token != null;

  // Helper untuk save/read yang support web
  Future<void> _saveToken(String token) async {
    if (kIsWeb) {
      final prefs = await SharedPreferences.getInstance();
      await prefs.setString('token', token);
    } else {
      await storage.write(key: 'token', value: token);
    }
  }

  Future<void> _saveUser(String userJson) async {
    if (kIsWeb) {
      final prefs = await SharedPreferences.getInstance();
      await prefs.setString('user', userJson);
    } else {
      await storage.write(key: 'user', value: userJson);
    }
  }

  Future<String?> _readToken() async {
    if (kIsWeb) {
      final prefs = await SharedPreferences.getInstance();
      return prefs.getString('token');
    } else {
      return await storage.read(key: 'token');
    }
  }

  Future<String?> _readUser() async {
    if (kIsWeb) {
      final prefs = await SharedPreferences.getInstance();
      return prefs.getString('user');
    } else {
      return await storage.read(key: 'user');
    }
  }

  Future<void> _deleteAuth() async {
    if (kIsWeb) {
      final prefs = await SharedPreferences.getInstance();
      await prefs.remove('token');
      await prefs.remove('user');
    } else {
      await storage.delete(key: 'token');
      await storage.delete(key: 'user');
    }
  }

  // Metode untuk login
  Future<Map<String, dynamic>> login(String username, String password) async {
    _isLoading = true;
    notifyListeners();

    try {      final response = await http.post(
        Uri.parse('${ApiConfig.baseUrl}${ApiConfig.login}'),
        headers: {'Content-Type': 'application/json'},
        body: jsonEncode({
          'username': username,
          'password': password,
        }),
      );

      _isLoading = false;
      notifyListeners();

      // Cetak response untuk debug      // Coba parse response
      Map<String, dynamic> data;
      try {
        data = jsonDecode(response.body);
      } catch (e) {        return {'success': false, 'message': 'Gagal memproses respon dari server'};
      }

      if (response.statusCode == 200 && data['success'] == true) {
        _currentUser = User.fromJson(data['data']['user']);
        _token = data['data']['access_token'];
        
        // Simpan token di storage aman
        await _saveToken(_token!);
        await _saveUser(jsonEncode(_currentUser?.toJson()));
        
        notifyListeners();
  return {'success': true, 'message': 'Masuk berhasil'};
      } else if (response.statusCode == 401) {
        return {'success': false, 'message': data['message'] ?? 'Nama pengguna atau kata sandi salah'};
      } else if (response.statusCode == 403) {
        return {'success': false, 'message': data['message'] ?? 'Akses ditolak'};
      }
      
      return {'success': false, 'message': 'Terjadi kesalahan pada server'};
    } catch (e) {
      _isLoading = false;
      notifyListeners();      return {'success': false, 'message': 'Terjadi kesalahan koneksi: $e'};
    }
  }

  // Metode untuk register
  Future<Map<String, dynamic>> register(String username, String password) async {
    _isLoading = true;
    notifyListeners();

    try {      final response = await http.post(
        Uri.parse('${ApiConfig.baseUrl}${ApiConfig.register}'),
        headers: {'Content-Type': 'application/json'},
        body: jsonEncode({
          'username': username,
          'password': password,
        }),
      );

      _isLoading = false;
      notifyListeners();

      // Cetak response untuk debug      // Coba parse response
      Map<String, dynamic> data;
      try {
        data = jsonDecode(response.body);
      } catch (e) {        return {'success': false, 'message': 'Gagal memproses respon dari server'};
      }
      
      if (response.statusCode == 201 && data['success'] == true) {
        _currentUser = User.fromJson(data['data']['user']);
        _token = data['data']['access_token'];
        
        // Simpan token di storage aman
        await _saveToken(_token!);
        await _saveUser(jsonEncode(_currentUser?.toJson()));
        
        notifyListeners();
  return {'success': true, 'message': 'Pendaftaran berhasil'};
      } else if (response.statusCode == 422) {
        // Handle validation error
  String errorMessage = 'Kesalahan validasi';
        if (data.containsKey('message')) {
          errorMessage = data['message'];
        }
        if (data.containsKey('data') && data['data'] is Map) {
          var errors = data['data'];
          if (errors.containsKey('username')) {
            errorMessage = 'Nama pengguna sudah digunakan';
          }
        }
        return {'success': false, 'message': errorMessage};
      }
      
      return {'success': false, 'message': 'Terjadi kesalahan pada server'};
    } catch (e) {
      _isLoading = false;
      notifyListeners();      return {'success': false, 'message': 'Terjadi kesalahan koneksi: $e'};
    }
  }

  // Metode untuk logout
  Future<bool> logout() async {
    _isLoading = true;
    notifyListeners();

    try {
      final response = await http.post(
        Uri.parse('${ApiConfig.baseUrl}${ApiConfig.logout}'),
        headers: {
          'Content-Type': 'application/json',
          'Authorization': 'Bearer $_token',
        },
      );

      await _deleteAuth();
      
      _currentUser = null;
      _token = null;
      
      _isLoading = false;
      notifyListeners();
      
      return response.statusCode == 200;
    } catch (e) {
      _isLoading = false;
      notifyListeners();
      
      // Hapus data lokal meskipun request gagal
      await _deleteAuth();
      _currentUser = null;
      _token = null;
      
      return false;
    }
  }

  // Metode untuk mendapatkan profil user
  Future<bool> getProfile() async {
    if (_token == null) return false;

    _isLoading = true;
    notifyListeners();

    try {
      final response = await http.get(
        Uri.parse('${ApiConfig.baseUrl}${ApiConfig.profile}'),
        headers: {
          'Content-Type': 'application/json',
          'Authorization': 'Bearer $_token',
        },
      );

      _isLoading = false;
      notifyListeners();

      if (response.statusCode == 200) {
        final data = jsonDecode(response.body);
        
        if (data['success'] == true) {
          _currentUser = User.fromJson(data['data']);
          notifyListeners();
          return true;
        }
      }
      
      return false;
    } catch (e) {
      _isLoading = false;
      notifyListeners();
      return false;
    }
  }

  // Metode untuk check token yang tersimpan
  Future<bool> checkAuth() async {
    try {
      final token = await _readToken();
      if (token == null) return false;

      _token = token;
      
      final userJson = await _readUser();
      if (userJson != null) {
        try {
          _currentUser = User.fromJson(jsonDecode(userJson));
        } catch (e) {
          await _deleteAuth();
          _token = null;
          _currentUser = null;
          return false;
        }
      }
      
      notifyListeners();
      
      // Selama ada token dan user data, anggap authenticated
      // Tidak perlu validasi ke server setiap kali
      // Server akan auto-reject request kalau token invalid
      return _token != null && _currentUser != null;
    } catch (e) {
      // Kalau ada error apapun (termasuk storage error), jangan logout
      // Return false hanya kalau memang tidak ada data
      return false;
    }
  }

  // Metode untuk mengubah profil
  Future<Map<String, dynamic>> updateProfile(String username) async {
    if (_token == null) return {'success': false, 'message': 'Tidak terautentikasi'};

    _isLoading = true;
    notifyListeners();

    try {
      final response = await http.put(
        Uri.parse('${ApiConfig.baseUrl}${ApiConfig.profile}'),
        headers: {
          'Content-Type': 'application/json',
          'Authorization': 'Bearer $_token',
        },
        body: jsonEncode({
          'username': username,
        }),
      );

      _isLoading = false;
      notifyListeners();      if (response.statusCode == 200) {
        final data = jsonDecode(response.body);
        
        if (data['success'] == true) {
          _currentUser = User.fromJson(data['data']);
          await _saveUser(jsonEncode(_currentUser?.toJson()));
          notifyListeners();
          return {'success': true, 'message': 'Profil berhasil diperbarui'};
        }
      }
      
      return {'success': false, 'message': 'Gagal memperbarui profil'};
    } catch (e) {
      _isLoading = false;
      notifyListeners();      return {'success': false, 'message': 'Terjadi kesalahan: $e'};
    }
  }

  // Metode untuk mengubah password
  Future<Map<String, dynamic>> changePassword(String currentPassword, String newPassword) async {
    if (_token == null) return {'success': false, 'message': 'Tidak terautentikasi'};

    _isLoading = true;
    notifyListeners();

    try {
      final response = await http.put(
        Uri.parse('${ApiConfig.baseUrl}${ApiConfig.changePassword}'),
        headers: {
          'Content-Type': 'application/json',
          'Authorization': 'Bearer $_token',
        },
        body: jsonEncode({
          'current_password': currentPassword,
          'password': newPassword,
        }),
      );

      _isLoading = false;
      notifyListeners();      if (response.statusCode == 200) {
        final data = jsonDecode(response.body);
        
        if (data['success'] == true) {
          return {'success': true, 'message': 'Kata sandi berhasil diubah'};
        } else {
          return {'success': false, 'message': data['message'] ?? 'Gagal mengubah kata sandi'};
        }
      } else if (response.statusCode == 422) {
        final data = jsonDecode(response.body);
        return {'success': false, 'message': data['message'] ?? 'Kata sandi saat ini tidak valid'};
      }
      
      return {'success': false, 'message': 'Gagal mengubah kata sandi'};
    } catch (e) {
      _isLoading = false;
      notifyListeners();      return {'success': false, 'message': 'Terjadi kesalahan: $e'};
    }
  }
}
