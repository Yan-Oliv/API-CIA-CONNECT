namespace App\Models;

use Illuminate\Database\Eloquent\Model;

abstract class BaseModel extends Model
{
    const CREATED_AT = null;
    const UPDATED_AT = 'last_update';
}
