import 'dart:convert';

import 'package:http/http.dart' as http;

import '../config/api_config.dart';
import '../models/booking.dart';
class BookingService {

  // Metode untuk mendapatkan daftar peminjaman
  Future<List<Booking>> getBookings(String token) async {
    try {
      final response = await http.get(
        Uri.parse('${ApiConfig.baseUrl}${ApiConfig.bookings}'),
        headers: {
          'Content-Type': 'application/json',
          'Authorization': 'Bearer $token',
        },
      );

      if (response.statusCode == 200) {
        final data = jsonDecode(response.body);
        
        if (data['success'] == true) {
          final List<dynamic> bookingsJson = data['data'];
          return bookingsJson.map((json) => Booking.fromJson(json)).toList();
        }
      }
      
      return [];
    } catch (e) {
      return [];
    }
  }

  // Metode untuk mendapatkan detail peminjaman
  Future<Booking?> getBookingDetail(int bookingId, String token) async {
    try {
      final response = await http.get(
        Uri.parse('${ApiConfig.baseUrl}${ApiConfig.bookings}/$bookingId'),
        headers: {
          'Content-Type': 'application/json',
          'Authorization': 'Bearer $token',
        },
      );

      if (response.statusCode == 200) {
        final data = jsonDecode(response.body);
        
        if (data['success'] == true) {
          return Booking.fromJson(data['data']);
        }
      }
      
      return null;
    } catch (e) {
      return null;
    }
  }

  // Metode untuk membuat peminjaman baru
  Future<Booking?> createBooking({
    required int roomId, 
    required String startDate, 
    required String endDate, 
    required String description,
    required String token,
  }) async {
    final requestBody = {
      'id_room': roomId,
      'tanggal_mulai': startDate,
      'tanggal_selesai': endDate,
      'keterangan': description,
    };

    final response = await http.post(
      Uri.parse('${ApiConfig.baseUrl}${ApiConfig.bookings}'),
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'Authorization': 'Bearer $token',
      },
      body: jsonEncode(requestBody),
    );

    // Sukses
    if (response.statusCode == 201 || response.statusCode == 200) {
      try {
        final data = jsonDecode(response.body);
        if (data['success'] == true) {
          return Booking.fromJson(data['data']);
        }
      } catch (_) {}
      return null;
    }

    // Gagal: coba tampilkan pesan error yang jelas dari server
    try {
      final data = jsonDecode(response.body);
      String? msg;
      if (data is Map<String, dynamic>) {
        if (data['message'] is String) msg = data['message'];
        if (msg == null && data['error'] is String) msg = data['error'];
        if (msg == null && data['errors'] is Map && (data['errors'] as Map).isNotEmpty) {
          final firstKey = (data['errors'] as Map).keys.first;
          final firstErr = (data['errors'][firstKey] as List).first;
          if (firstErr is String) msg = firstErr;
        }
      }
      if (msg != null) {
        throw Exception(msg);
      }
    } catch (_) {
      // ignore JSON parse errors, fallback to status code
    }

    throw Exception('Gagal membuat peminjaman (kode ${response.statusCode})');
  }

  // Metode baru untuk membuat peminjaman dengan tipe (hourly/daily)
  Future<Booking?> createBookingWithType({
    required int roomId,
    required String tipeBooking,
    required double harga,
    required int durasi,
    required String startDateTime,
    required String endDateTime,
    required String description,
    required String token,
  }) async {
    final requestBody = {
      'id_room': roomId,
      'tipe_booking': tipeBooking,
      'harga': harga,
      'durasi': durasi,
      'tanggal_mulai': startDateTime,
      'tanggal_selesai': endDateTime,
      'keterangan': description,
    };

    final response = await http.post(
      Uri.parse('${ApiConfig.baseUrl}${ApiConfig.bookings}'),
      headers: {
        'Content-Type': 'application/json',
        'Authorization': 'Bearer $token',
      },
      body: jsonEncode(requestBody),
    );

    // Sukses
    if (response.statusCode == 201 || response.statusCode == 200) {
      try {
        final data = jsonDecode(response.body);
        if (data['success'] == true) {
          return Booking.fromJson(data['data']);
        }
      } catch (_) {}
      return null;
    }

    // Gagal: coba tampilkan pesan error yang jelas dari server
    try {
      final data = jsonDecode(response.body);
      String? msg;
      if (data is Map<String, dynamic>) {
        if (data['message'] is String) msg = data['message'];
        if (msg == null && data['error'] is String) msg = data['error'];
        if (msg == null && data['errors'] is Map && (data['errors'] as Map).isNotEmpty) {
          final firstKey = (data['errors'] as Map).keys.first;
          final firstErr = (data['errors'][firstKey] as List).first;
          if (firstErr is String) msg = firstErr;
        }
      }
      if (msg != null) {
        throw Exception(msg);
      }
    } catch (e) {
      if (e is Exception) rethrow;
      // ignore JSON parse errors, fallback to status code
    }

    throw Exception('Gagal membuat peminjaman (kode ${response.statusCode})');
  }

  // Metode untuk memperbarui peminjaman
  Future<Booking?> updateBooking({
    required int bookingId,
    required int roomId, 
    required String startDate, 
    required String endDate, 
    required String description,
    required String token,
  }) async {
    try {
      final response = await http.put(
        Uri.parse('${ApiConfig.baseUrl}${ApiConfig.bookings}/$bookingId'),
        headers: {
          'Content-Type': 'application/json',
          'Authorization': 'Bearer $token',
        },
        body: jsonEncode({
          'id_room': roomId,
          'tanggal_mulai': startDate,
          'tanggal_selesai': endDate,
          'keterangan': description,
        }),
      );

      if (response.statusCode == 200) {
        final data = jsonDecode(response.body);
        
        if (data['success'] == true) {
          return Booking.fromJson(data['data']);
        }
      }
      
      return null;
    } catch (e) {
      return null;
    }
  }

  // Metode untuk membatalkan peminjaman
  Future<bool> cancelBooking(int bookingId, String token) async {
    try {
      final response = await http.delete(
        Uri.parse('${ApiConfig.baseUrl}${ApiConfig.bookings}/$bookingId'),
        headers: {
          'Content-Type': 'application/json',
          'Authorization': 'Bearer $token',
        },
      );

      if (response.statusCode == 200) {
        final data = jsonDecode(response.body);
        return data['success'] == true;
      }
      
      return false;
    } catch (e) {
      return false;
    }
  }

  // Metode untuk menyetujui peminjaman (petugas/admin)
  Future<bool> approveBooking(int bookingId, String token) async {
    try {
      final response = await http.put(
        Uri.parse('${ApiConfig.baseUrl}/petugas/bookings/$bookingId/approve'),
        headers: {
          'Content-Type': 'application/json',
          'Authorization': 'Bearer $token',
        },
      );

      if (response.statusCode == 200) {
        final data = jsonDecode(response.body);
        return data['success'] == true;
      }
      
      return false;
    } catch (e) {
      return false;
    }
  }

  // Metode untuk menolak peminjaman (petugas/admin)
  Future<bool> rejectBooking(int bookingId, String token, String reason) async {
    try {
      final response = await http.put(
        Uri.parse('${ApiConfig.baseUrl}/petugas/bookings/$bookingId/reject'),
        headers: {
          'Content-Type': 'application/json',
          'Authorization': 'Bearer $token',
        },
        body: jsonEncode({
          'alasan_tolak': reason,
        }),
      );

      if (response.statusCode == 200) {
        final data = jsonDecode(response.body);
        return data['success'] == true;
      }
      
      return false;
    } catch (e) {
      return false;
    }
  }
}

