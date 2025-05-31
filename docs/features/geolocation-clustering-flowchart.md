flowchart TD
    Start([Start: geo:find-nearby command]) --> ValidateInput{Validate Input Parameters}
    
    ValidateInput -->|Invalid| ErrorExit[❌ Error: Invalid parameters]
    ValidateInput -->|Valid| FindParent[Find Parent Record by ID]
    
    FindParent -->|Not Found| ErrorParent[❌ Error: Parent not found]
    FindParent -->|Found| CheckCopyGeo{--copy-geo flag?}
    
    CheckCopyGeo -->|Yes| ValidateParentGeo{Parent has geo_location?}
    CheckCopyGeo -->|No| BuildQuery[Build Children Query]
    
    ValidateParentGeo -->|No| ErrorNoGeo[❌ Error: Parent has no geo_location]
    ValidateParentGeo -->|Yes| BuildQuery
    
    BuildQuery --> StoreOriginal[Store Original Parent Coordinates]
    StoreOriginal --> CalcBounds[Calculate Bounding Box<br/>deltaLat & deltaLon]
    CalcBounds --> BaseQuery[Base Query: Within Bounds<br/>+ Haversine Distance Filter]
    
    BaseQuery --> CheckNullGeo{--search-null-geo flag?}
    CheckNullGeo -->|Yes| AddNullFilter[Add Filter:<br/>attempts IS NULL<br/>AND geo_location IS NULL]
    CheckNullGeo -->|No| ExecuteQuery[Execute Query]
    AddNullFilter --> ExecuteQuery
    
    ExecuteQuery --> CheckChildren{Children Found?}
    CheckChildren -->|No| OutputEmpty[Output: No children found]
    CheckChildren -->|Yes| CheckMark{--mark flag?}
    
    CheckMark -->|Yes| CalcNormalized[Calculate Normalized Location<br/>Based on Strategy]
    CheckMark -->|No| CheckCopy{--copy-geo flag?}
    
    CalcNormalized --> PreserveRaw[Preserve Raw Data<br/>if not already set]
    PreserveRaw --> ApplyNormalized[Apply Normalized Values<br/>to Parent]
    ApplyNormalized --> MarkParent[Set parent_id = id<br/>is_nearby_processed = true]
    MarkParent --> UpdateChildren[Update Children<br/>parent_id = parent.id<br/>is_nearby_processed = true]
    UpdateChildren --> CheckCopy
    
    CheckCopy -->|Yes| ProcessCopyLoop[Process Each Child]
    CheckCopy -->|No| CalcDistances[Calculate Distances<br/>for Output]
    
    ProcessCopyLoop --> CheckChildGeo{Child has<br/>geo_location?}
    CheckChildGeo -->|No| PreserveAttempts[Preserve raw_attempts<br/>if not already set]
    CheckChildGeo -->|Yes| NextChild[Next Child]
    
    PreserveAttempts --> CopyGeoData[Copy geo_location<br/>from Parent]
    CopyGeoData --> SetAttempts[Set attempts = 100]
    SetAttempts --> SaveChild[Save Child]
    SaveChild --> NextChild
    
    NextChild --> MoreChildren{More Children?}
    MoreChildren -->|Yes| ProcessCopyLoop
    MoreChildren -->|No| ShowCopyCount[Show: Copied geo_location<br/>to X children]
    
    ShowCopyCount --> CalcDistances
    CalcDistances --> BuildOutput[Build JSON Output]
    
    BuildOutput --> OutputSuccess[✅ Output Success JSON<br/>with all details]
    
    %% Strategy Decision Diamond
    CalcNormalized --> StrategyChoice{Strategy Type?}
    StrategyChoice -->|centroid| CalcCentroid[Calculate Average<br/>Coordinates]
    StrategyChoice -->|best_accuracy| FindBestAccuracy[Find Point with<br/>Fewest Attempts]
    StrategyChoice -->|most_recent| FindMostRecent[Find Point with<br/>Highest ID]
    
    CalcCentroid --> PreserveRaw
    FindBestAccuracy --> PreserveRaw
    FindMostRecent --> PreserveRaw
    
    %% End states
    ErrorExit --> End([End])
    ErrorParent --> End
    ErrorNoGeo --> End
    OutputEmpty --> End
    OutputSuccess --> End
    
    %% Styling
    classDef startEnd fill:#e1f5fe,stroke:#01579b,stroke-width:2px
    classDef process fill:#f3e5f5,stroke:#4a148c,stroke-width:2px
    classDef decision fill:#fff3e0,stroke:#e65100,stroke-width:2px
    classDef error fill:#ffebee,stroke:#c62828,stroke-width:2px
    classDef success fill:#e8f5e8,stroke:#2e7d32,stroke-width:2px
    classDef database fill:#e3f2fd,stroke:#1976d2,stroke-width:2px
    
    class Start,End startEnd
    class FindParent,StoreOriginal,CalcBounds,BaseQuery,ExecuteQuery,CalcNormalized,PreserveRaw,ApplyNormalized,MarkParent,UpdateChildren,ProcessCopyLoop,PreserveAttempts,CopyGeoData,SetAttempts,SaveChild,CalcDistances,BuildOutput,CalcCentroid,FindBestAccuracy,FindMostRecent process
    class ValidateInput,CheckCopyGeo,ValidateParentGeo,CheckNullGeo,CheckChildren,CheckMark,CheckCopy,CheckChildGeo,MoreChildren,StrategyChoice decision
    class ErrorExit,ErrorParent,ErrorNoGeo error
    class OutputSuccess,ShowCopyCount success
    class AddNullFilter,NextChild database