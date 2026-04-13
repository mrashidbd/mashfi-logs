# L_REF and D_REF Fields Documentation

## Overview
Two new fields have been added to the timber log tracking system to account for volume adjustments:

### Fields Added

#### 1. **L_REF (Loss Reference)**
- **Type**: Decimal (10, 6)
- **Nullable**: Yes
- **Purpose**: Represents volume loss due to various factors (damage, splits, unusable portions)
- **Tables**: 
  - `logs` table: `l_ref` - Original/declared loss value
  - `inspections` table: `actual_l_ref` - Surveyor's measured loss value

#### 2. **D_REF (Decomposed Reference)**
- **Type**: Decimal (10, 6)
- **Nullable**: Yes
- **Purpose**: Represents volume reduction due to decomposition, rot, or decay
- **Tables**:
  - `logs` table: `d_ref` - Original/declared decomposed value
  - `inspections` table: `actual_d_ref` - Surveyor's measured decomposed value

## Database Structure

### Logs Table
```php
$table->decimal('vol_cbm', 10, 6)->nullable();      // Total volume
$table->decimal('l_ref', 10, 6)->nullable();        // Loss reference
$table->decimal('d_ref', 10, 6)->nullable();        // Decomposed reference
```

### Inspections Table
```php
$table->decimal('actual_vol_cbm', 10, 6)->nullable();     // Actual measured volume
$table->decimal('actual_l_ref', 10, 6)->nullable();       // Actual loss
$table->decimal('actual_d_ref', 10, 6)->nullable();       // Actual decomposition
```

## Import Support

The Excel/CSV import functionality has been updated to support these fields:

### Expected Column Names in Import Files
- `l_ref` - Maps to `logs.l_ref`
- `d_ref` - Maps to `logs.d_ref`

Both fields are optional (nullable) during import.

## Future Volume Calculation

> **Note**: Calculations are not yet implemented, but the provision is in place.

### Potential Formula
```
Net Usable Volume = vol_cbm - l_ref - d_ref
```

or

```
Net Usable Volume = vol_cbm * (1 - (l_ref + d_ref))
```

The exact calculation method will depend on whether L_REF and D_REF are:
- **Absolute values** (in m³ to subtract)
- **Percentages** (to multiply)
- **Other measurement units**

## Implementation Status

✅ **Completed**:
- Database migrations (both initial and update migration)
- Model fillable arrays (Log and Inspection models)
- Import functionality (VesselImport class)
- Column structure in place

⏳ **Pending**:
- Volume calculation logic
- UI display in inspection forms
- Reporting adjustments for net volume
- Business rules validation (e.g., L_REF + D_REF cannot exceed vol_cbm)

## Files Modified

1. **Database Migrations**:
   - `2026_01_28_021501_create_logs_table.php`
   - `2026_01_28_021502_create_inspections_table.php`
   - `2026_01_31_192252_add_l_ref_and_d_ref_to_logs_and_inspections_tables.php` (new)

2. **Models**:
   - `app/Models/Log.php`
   - `app/Models/Inspection.php`

3. **Import**:
   - `app/Imports/VesselImport.php`

## Usage Examples

### During Import
The Excel file should have columns named `l_ref` and `d_ref`:

| tag_no | species | volume_cbm | l_ref | d_ref |
|--------|---------|------------|-------|-------|
| AD001  | AZOBE   | 6.472725   | 0.150 | 0.050 |

### During Inspection
When a surveyor inspects a log and finds loss or decomposition, they can record:
- `actual_l_ref`: Measured loss value
- `actual_d_ref`: Measured decomposition value

These will be stored separately from the original declared values for comparison.

## Next Steps

To implement volume calculations:
1. Define business rules for L_REF and D_REF (absolute vs percentage)
2. Add computed properties or accessors to models
3. Update inspection UI to show/edit these values
4. Update reports to show net usable volume
5. Add validation rules to ensure values are logical
