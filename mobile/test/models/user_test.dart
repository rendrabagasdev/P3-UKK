import 'package:flutter_test/flutter_test.dart';

import 'package:ukk_room_booking/models/user.dart';

void main() {
  group('User Model Tests', () {
    test('User model can be created from JSON', () {
      final Map<String, dynamic> json = {
        'id_user': 1,
        'username': 'testuser',
        'role': 1,
        'created_at': '2023-01-01 00:00:00',
        'updated_at': '2023-01-01 00:00:00',
      };

      final user = User.fromJson(json);

      expect(user.idUser, 1);
      expect(user.username, 'testuser');
      expect(user.role, 1);
    });

    test('User model converts to JSON correctly', () {
      final user = User(
        idUser: 1,
        username: 'testuser',
        role: 1,
      );

      final json = user.toJson();

      expect(json['id'], 1);
      expect(json['name'], 'Test User');
      expect(json['email'], 'test@example.com');
      expect(json['role'], 'user');
    });
  });
}
