import 'dart:convert';

import 'package:http/http.dart' as http;

import '../config/api_config.dart';
import '../models/room.dart';
class RoomService {

  // Metode untuk mendapatkan daftar ruangan
  Future<List<Room>> getRooms() async {
    try {
      final response = await http.get(
        Uri.parse('${ApiConfig.baseUrl}${ApiConfig.rooms}'),
        headers: {'Content-Type': 'application/json'},
      );

      if (response.statusCode == 200) {
        final data = jsonDecode(response.body);
        
        if (data['success'] == true) {
          final List<dynamic> roomsJson = data['data'];
          return roomsJson.map((json) => Room.fromJson(json)).toList();
        }
      }
      
      return [];
    } catch (e) {
      return [];
    }
  }

  // Metode untuk mendapatkan detail ruangan
  Future<Room?> getRoomDetail(int roomId) async {
    try {
      final response = await http.get(
        Uri.parse('${ApiConfig.baseUrl}${ApiConfig.rooms}/$roomId'),
        headers: {'Content-Type': 'application/json'},
      );

      if (response.statusCode == 200) {
        final data = jsonDecode(response.body);
        
        if (data['success'] == true) {
          return Room.fromJson(data['data']);
        }
      }
      
      return null;
    } catch (e) {
      return null;
    }
  }

  // Metode untuk memeriksa ketersediaan ruangan
  Future<bool> checkRoomAvailability(int roomId, String startDate, String endDate, String token) async {
    try {
      final response = await http.get(
        Uri.parse('${ApiConfig.baseUrl}${ApiConfig.rooms}/$roomId/check-availability?tanggal_mulai=$startDate&tanggal_selesai=$endDate'),
        headers: {
          'Content-Type': 'application/json',
          'Authorization': 'Bearer $token',
        },
      );

      if (response.statusCode == 200) {
        final data = jsonDecode(response.body);
        
        if (data['success'] == true) {
          return data['data']['is_available'];
        }
      }
      
      return false;
    } catch (e) {
      return false;
    }
  }
}
