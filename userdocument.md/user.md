# GIS Map Viewer – Complete Cheat Sheet

**Open App:** `index.html` → default view: Mumbai, zoom 12  


## Navigation
- **Home:** Return to default view  
- **Zoom:** + / - buttons or scroll  
- **My Location:** Show GPS position  
- **Search:** Top-left search bar  

## Upload Files
- **Formats:** Shapefile (.zip), GeoJSON (.json/.geojson), KML (.kml)  
- **Limits:** Max 50 MB, 10,000 features  
- **How:** Sidebar Upload → select file → wait → features appear + attribute table opens  

## Draw / Edit Features
- **Point:** Click map → add optional name  
- **Line:** Click vertices → double-click to finish → add details  
- **Polygon:** Click corners → double-click to close → add properties  
- **Modify:** Select feature → drag vertices → click elsewhere to finish  
- **Move:** Select feature → drag → release  
- **Delete:** Select feature → Delete button or via Attribute Table  

## Attribute Table
- Open: Sidebar  Data  
- **Actions:** Edit values, select features, center on feature, delete, export  
- **Custom Fields:** Text (max 500 chars), Number, Checkbox, Date (YYYY-MM-DD), Image URL, Dropdown  
- **Change Color:** Click color picker in table column or features get random colors when drawn  

## Geoprocessing / Analysis
- **Buffer:** Create zone around features  
  1. Select feature  
  2. Tools → Buffer  
  3. Enter distance & unit → Create Buffer  
- **Intersect:** Find overlapping area of polygons  
  - Only polygons, must overlap  
- **Dissolve:** Merge multiple features  
  - Choose field or "Dissolve All"  

## Layers Panel
- Sidebar  Layers → toggle visibility, adjust opacity, reorder, delete  
- Layer Types: Drawn features, Shapefile, GeoJSON, KML, Database  

## Basemaps
-  OpenStreetMap – streets/general  
-  Topographic – terrain/elevation  
-  Light Map – clean/minimal  
-  Satellite – aerial imagery  

## Database Connection
- Supported: MySQL (3306), PostgreSQL (5432)  
- Steps:
  1. Sidebar Database → enter host, port, db, username, password  
  2. Test connection → Connect & Load Data  
  3. Select table → choose lat/lng columns (optional: name & attributes) → Load Data  

## Export Features
- **Drawn features:** Editor → Export → `my-drawing-[timestamp].geojson`  
- **All features:** Attribute Table → Export → `all-features-[timestamp].geojson`  
- **Format:** GeoJSON (EPSG:4326), includes all attributes & geometry  



## Measurement Tools
- **Scale line:** Bottom-right → units: m/km, ft/mi, nautical, US  
- **Distance:** Use Buffer tool → distance shown in attributes  



## Navigation Tools
- **North arrow:** Bottom-left → click rotate 45°, right-click reset north  
- **Search:** Top-left → powered by OpenStreetMap  



## Mobile Support
- Works on tablets & phones  
- Touch gestures: pinch zoom, two-finger rotate, tap to select  
- Sidebar auto-adjusts  



## Settings & Preferences
- Auto-save drawings, custom attributes, and layer settings (cleared on refresh)  
- Max features: 10,000, Max file size: 50 MB, Browser storage ~5 MB  



## Troubleshooting
- **No features found:** Upload file or draw manually  
- **Please select features:** Click map or check box in Attribute Table  
- **File upload fails:** Check size & format; Shapefile must include `.shp`, `.shx`, `.dbf`  
- **Buffer/Intersect fails:** Check feature type, overlap, and distance value  



## Example Workflows
1. **Mark Building + Safety Zone:** Polygon → Buffer 100m → safety zone  
2. **Upload Shapefile + Dissolve:** Upload → Table → Select 2+ → Dissolve → merged area  
3. **Database Points + Export:** Connect → Load points → Attribute Table → Export GeoJSON  



## Tips & Best Practices
- Save work frequently → Export to GeoJSON  
- Use Buffer to convert points → polygons for analysis  
- Check feature limits (max 10,000)  
- Simplify complex shapes before uploading large files  
- Organize datasets with Layers Panel  
- Add custom attributes before drawing features  

**Document Version:** 1.0  
**Last Updated:** February 2026  
**Application:** GIS Map Viewer – Enhanced Edition
