## Ngôn ngữ
Trả lời bằng tiếng Việt

## Nguyên tắc thiết kế
- **SOLID**: Thiết kế OOP rõ ràng, linh hoạt
- **KISS**: Giữ mọi thứ đơn giản
- **YAGNI**: Không làm thứ chưa cần
- **DRY**: Không lặp lại logic
- **TDA**: Ra lệnh cho object, không lấy dữ liệu ra xử lý

## API Structure

Xem tài liệu chi tiết tại: `docs/`

### Kiến trúc tầng (Layered Architecture)
- **Controller** (`Http/Controllers/Api`) - Xử lý HTTP request/response
- **Service** (`Services`) - Logic nghiệp vụ
- **Repository** (`Repositories`) - Truy cập dữ liệu
- **Model** (`Models`) - Eloquent models
- **Request** (`Http/Requests`) - Validation input
- **Resource** (`Http/Resources`) - Transform output
- **DTO** (`DTOs`) - Data transfer object
- **Exception** (`Exceptions`) - Custom exceptions

### Response format
```json
{
  "success": true|false,
  "message": "...",
  "data": {...}|[...],
  "errors": {...}
}
```

### Khi tạo feature mới
1. Tạo Migration & Model
2. Tạo Repository + Service
3. Tạo Controller
4. Tạo Form Requests (StoreRequest, UpdateRequest)
5. Tạo Resources (Resource, Collection)
6. Thêm routes vào `routes/api/v1.php`
7. Test API

### Routes naming
- `GET /api/v1/users` - List
- `POST /api/v1/users` - Create
- `GET /api/v1/users/{id}` - Show
- `PUT/PATCH /api/v1/users/{id}` - Update
- `DELETE /api/v1/users/{id}` - Delete

## 📚 Documentation

Xem `docs/README.md` để tiếp cận tài liệu chi tiết:
- `docs/api/STRUCTURE.md` - Kiến trúc API
- `docs/api/RESPONSE_FORMAT.md` - Format response
- `docs/api/VERSIONING.md` - Versioning strategy
- `docs/api/AUTHENTICATION.md` - Auth & Authorization
- `docs/api/ERROR_HANDLING.md` - Error handling
- `docs/guides/GETTING_STARTED.md` - Hướng dẫn bắt đầu
- `docs/guides/CREATING_FEATURES.md` - Tạo feature
- `docs/guides/BEST_PRACTICES.md` - Best practices
