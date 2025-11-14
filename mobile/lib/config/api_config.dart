import 'package:flutter/foundation.dart' show kIsWeb;
import 'dart:io' show Platform;

class ApiConfig {
  // Prefer setting from --dart-define=API_BASE_URL=http://<host>:<port>/api
  static const String _envBaseUrl = String.fromEnvironment('API_BASE_URL');

  // Resolve base URL at runtime depending on platform and environment
  static String get baseUrl {
    if (_envBaseUrl.isNotEmpty) return _envBaseUrl;

    if (kIsWeb) {
      // Saat berjalan di Flutter Web, backend Laravel biasanya di port 8000
      // sementara dev server Flutter berjalan di port dinamis (contoh: 65489).
      // Agar tidak salah ke origin Flutter, kita arahkan eksplisit ke port 8000.
      final uri = Uri.base;
      final host = (uri.host == 'localhost') ? '127.0.0.1' : uri.host;
      return '${uri.scheme}://$host:8000/api';
    }

    // For Android emulator use the special 10.0.2.2 host
    if (Platform.isAndroid) return 'http://10.0.2.2:8000/api';

    // iOS simulator/macOS/Windows/Linux default
    return 'http://127.0.0.1:8000/api';
  }

  // Endpoint API
  static const String login = '/login';
  static const String register = '/register';
  static const String logout = '/logout';
  static const String profile = '/profile';
  static const String changePassword = '/change-password';
  static const String rooms = '/rooms';
  static const String bookings = '/bookings';
}
