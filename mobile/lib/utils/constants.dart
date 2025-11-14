import 'package:flutter/material.dart';

class AppColors {
  // Web-matching theme colors
  static const Color headerBg = Color(0xFF374151); // Gray 700 - for AppBar headers
  static const Color primary = Color(0xFF2563EB); // Blue 600 - for primary buttons
  static const Color error = Color(0xFFEF4444); // Red 500 - for delete/error buttons
  static const Color success = Color(0xFF10B981); // Green 500 - for success states
  
  // Supporting colors
  static const Color primaryDark = Color(0xFF1E40AF); // Blue 800
  static const Color primaryLight = Color(0xFF3B82F6); // Blue 500
  static const Color accent = Color(0xFF60A5FA); // Blue 400

  // Neutral colors
  static const Color white = Color(0xFFFFFFFF);
  static const Color black = Color(0xFF000000);
  static const Color grey = Color(0xFF9E9E9E);
  static const Color lightGrey = Color(0xFFF5F5F5);
  static const Color darkGrey = Color(0xFF616161);

  // Status colors
  static const Color warning = Color(0xFFF59E0B); // Amber 500
  static const Color info = Color(0xFF3B82F6); // Blue 500
}

class AppTheme {
  static ThemeData lightTheme = ThemeData(
    primarySwatch: Colors.blue,
    scaffoldBackgroundColor: Colors.white,
    appBarTheme: const AppBarTheme(
      backgroundColor: AppColors.headerBg, // Gray header matching web
      foregroundColor: AppColors.white,
      elevation: 2,
      centerTitle: true,
    ),
    elevatedButtonTheme: ElevatedButtonThemeData(
      style: ElevatedButton.styleFrom(
        backgroundColor: AppColors.primary, // Blue buttons matching web
        foregroundColor: AppColors.white,
        padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 12),
        textStyle: const TextStyle(
          fontWeight: FontWeight.bold,
          fontSize: 16,
        ),
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(8),
        ),
      ),
    ),
    textButtonTheme: TextButtonThemeData(
      style: TextButton.styleFrom(
        foregroundColor: AppColors.primary,
      ),
    ),
    inputDecorationTheme: InputDecorationTheme(
      border: OutlineInputBorder(
        borderRadius: BorderRadius.circular(8),
      ),
      focusedBorder: OutlineInputBorder(
        borderSide: const BorderSide(color: AppColors.primary, width: 2),
        borderRadius: BorderRadius.circular(8),
      ),
    ),
    fontFamily: 'Poppins',
  );
}

class AppDimensions {
  static const double paddingXS = 4.0;
  static const double paddingS = 8.0;
  static const double paddingM = 16.0;
  static const double paddingL = 24.0;
  static const double paddingXL = 32.0;

  static const double radiusS = 4.0;
  static const double radiusM = 8.0;
  static const double radiusL = 16.0;

  static const double iconSizeS = 16.0;
  static const double iconSizeM = 24.0;
  static const double iconSizeL = 32.0;
  static const double iconSizeXL = 48.0;
}

class AppStrings {
  static const String appName = 'Aplikasi Peminjaman Ruangan';

  // Auth Strings
  static const String login = 'Masuk';
  static const String register = 'Daftar';
  static const String username = 'Nama Pengguna';
  static const String password = 'Kata Sandi';
  static const String confirmPassword = 'Konfirmasi Kata Sandi';
  static const String forgotPassword = 'Lupa Kata Sandi?';
  static const String noAccount = 'Belum punya akun?';
  static const String haveAccount = 'Sudah punya akun?';

  // Home Strings
  static const String rooms = 'Daftar Ruangan';
  static const String myBookings = 'Peminjaman Saya';
  static const String profile = 'Profil';
  static const String logout = 'Keluar';

  // Room Strings
  static const String roomDetail = 'Detail Ruangan';
  static const String bookRoom = 'Pinjam Ruangan';
  static const String location = 'Lokasi';
  static const String capacity = 'Kapasitas';
  static const String description = 'Deskripsi';

  // Booking Strings
  static const String bookingForm = 'Formulir Peminjaman';
  static const String startDate = 'Tanggal Mulai';
  static const String endDate = 'Tanggal Selesai';
  static const String notes = 'Keterangan';
  static const String submit = 'Pinjam Ruangan';
  static const String cancel = 'Batal';
  static const String update = 'Perbarui';
  static const String delete = 'Hapus';
}

class AppConstants {
  static const String apiUrl = 'http://127.0.0.1:8000/api';
  static const Color headerColor = AppColors.headerBg;
  static const Color primaryColor = AppColors.primary;
}
