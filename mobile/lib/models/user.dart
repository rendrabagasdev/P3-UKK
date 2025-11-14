class User {
  final int idUser;
  final String username;
  final int role;
  final String? createdAt;
  final String? updatedAt;

  User({
    required this.idUser,
    required this.username,
    required this.role,
    this.createdAt,
    this.updatedAt,
  });

  factory User.fromJson(Map<String, dynamic> json) {
    return User(
      idUser: json['id_user'] ?? 0,
      username: json['username'] ?? '',
      role: json['role'] ?? 0,
      createdAt: json['created_at'],
      updatedAt: json['updated_at'],
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id_user': idUser,
      'username': username,
      'role': role,
      'created_at': createdAt,
      'updated_at': updatedAt,
    };
  }
}
