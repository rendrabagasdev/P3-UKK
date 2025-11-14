import 'package:flutter/foundation.dart' show kIsWeb;
import 'dart:io' show Platform;

class ApiConfig {
  // Allow override from CLI: --dart-define=API_BASE_URL=http://<host>:<port>/api
  static const String _envBaseUrl = String.fromEnvironment('API_BASE_URL');

  // URL dasar untuk API Laravel (dinamis sesuai platform)
  static String get baseUrl {
    if (_envBaseUrl.isNotEmpty) return _envBaseUrl;

    if (kIsWeb) {
      final uri = Uri.base;
      final origin = uri.hasPort ? '${uri.scheme}://${uri.host}:${uri.port}' : '${uri.scheme}://${uri.host}';
      return '$origin/api';
    }
    if (Platform.isAndroid) return 'http://10.0.2.2:8000/api';
    return 'http://127.0.0.1:8000/api';
  }
  
  // Endpoint untuk autentikasi
  static const String login = '/login';
  static const String register = '/register';
  static const String logout = '/logout';
  static const String profile = '/profile';
  
  // Endpoint untuk ruangan
  static const String rooms = '/rooms';
  static const String checkAvailability = '/check-availability';
  
  // Endpoint untuk peminjaman
  static const String bookings = '/bookings';
}
