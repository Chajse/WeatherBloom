import { Component, OnDestroy, OnInit, ViewEncapsulation } from '@angular/core';
import { FormBuilder, ReactiveFormsModule, Validators } from '@angular/forms';
import { RouterOutlet } from '@angular/router';
import { WeatherService } from './services/weather.service';
import { Observable, Observer, Subscription, debounceTime, distinctUntilChanged } from 'rxjs';
import Swal from 'sweetalert2';
import { CommonModule } from '@angular/common';
import { MatTooltipModule } from '@angular/material/tooltip';
import { MatButtonModule } from '@angular/material/button';
import { MatMenuModule } from '@angular/material/menu';
import { HttpClient } from '@angular/common/http';

@Component({
  selector: 'app-root',
  standalone: true,
  imports: [RouterOutlet, ReactiveFormsModule, CommonModule, MatTooltipModule, MatMenuModule, MatButtonModule],
  templateUrl: './app.component.html',
  styleUrl: './app.component.css',
  encapsulation: ViewEncapsulation.None
})
export class AppComponent implements OnInit, OnDestroy {
  title = 'WeatherBloom';
  searchForm: any;
  private subscriptions = new Subscription();
  weatherData: any;
  forecastData: any;
  loading: boolean = false;
  loadingSuggestions: boolean = false;
  metricSystem: any = '&units=metric'; // My default metric system.
  userLocation:any;
  showAdvanced:boolean = false;
  suggestions:any
  inputFocused: boolean = false;
  clickingSuggestion = false;
  showNotFoundMessage = false;


  constructor(private http: HttpClient,private builder: FormBuilder, private weather: WeatherService){  
    this.searchForm = builder.group({
      city: ['', Validators.required],
      metricSystem: [this.metricSystem]
    })

    const metricSystem:any = localStorage.getItem('metricSystem');
    if(metricSystem && metricSystem !== 'null'){
      this.metricSystem = metricSystem;
    }

    const showAdvancedString:any = localStorage.getItem('showAdvanced');
    if(showAdvancedString){
      this.showAdvanced = JSON.parse(showAdvancedString);
    }
    
  }
  
  ngOnInit(): void {   
    this.getUserLocation(); 

    this.searchForm.get('city')?.valueChanges
    .pipe(
      // wait for a bit.
      debounceTime(200),
      // only trigger if value changes.
      distinctUntilChanged()
    )
    .subscribe((value: any) => {
      if (value && value.length > 2) {
        this.loadingSuggestions = true;
        this.showNotFoundMessage = false;
        this.weather.geocodeByCity({ city: value }).subscribe(
          (res: any) => {
            console.log(res);
            this.suggestions = res.payload;
            console.log(this.suggestions)
            this.loadingSuggestions = false;
            this.showNotFoundMessage = this.suggestions.length === 0;
          },
          error => {
            console.error('Error:', error);
            this.loadingSuggestions = false;
          }
        );
      } else {
        this.suggestions = [];
        this.showNotFoundMessage = false;
      }
    });

    document.addEventListener('click', this.onDocumentClick.bind(this));
  }

  onDocumentClick(event: MouseEvent) {
    if (!this.inputFocused || this.clickingSuggestion) {
      return;
    }
    const target = event.target as HTMLElement;
    if (!target.closest('.suggestion-container')) {
      this.inputFocused = false;
    }
  }

  onInputFocus() {
    this.inputFocused = true;
  }

  onInputBlur() {
    if (!this.clickingSuggestion) {
      this.inputFocused = false;
    }
  }

  onSuggestionMouseDown() {
    this.clickingSuggestion = true;
  }

  onSuggestionMouseUp() {
    this.clickingSuggestion = false;
  }

  onSuggestionClick(location: any) {
    this.inputFocused = false;
    this.clickingSuggestion = false;
    // Handle suggestion click
    console.log(location);
    this.searchByLocation(location.lat, location.lon)
  }

  getUserLocation() {
    navigator.geolocation.getCurrentPosition(
      (position) => {
          const userLocation = {
            lat: position.coords.latitude,
            lon: position.coords.longitude,
          };        
        console.log(userLocation);
        this.searchByLocation(userLocation.lat, userLocation.lon)
      }),
      {
        enableHighAccuracy: true,
        // timeout: 30000,
        timeout: 5000,
        maximumAge: 0
      }
  }

  searchByLocation(lat: any = null, lon: any = null) {
    this.loading = true;
        let userLocation;
          userLocation = {
            lat: lat,
            lon: lon,
            metricSystem: this.metricSystem
          };        
        this.subscriptions.add(
          this.weather.searchByLocation(userLocation).subscribe(
            (res: any) => {
              this.weatherData = res.payload;
              console.log(this.weatherData)
              this.loading = false;
            },
            (error) => {
              console.error('Error fetching weather:', error);
              this.loading = false;
            }
          )
        );      
  }

  searchByCity(){
    this.searchForm.patchValue({
      metricSystem: this.metricSystem
    })
    if(!this.searchForm.valid){
      Swal.fire({
        title: "Please enter a City",
        icon: "warning"
      })
      return
    }
    this.subscriptions.add(
      this.weather.searchByCity(this.searchForm.value).subscribe((res:any)=>{
        this.weatherData = res.payload;
        console.log("City:")
        console.log(this.weatherData)
      }, error =>{
        switch (error.status){
          case 404:
            Swal.fire({
              title: "City not found",
              text: `${error.error.status.message}`,
              icon: "warning",
              timer: 2000,
              timerProgressBar: true,
            })
          break;
          case 400:
            Swal.fire({
              title: "Invalid City",
              text: `Please enter a valid city.`,
              icon: "warning",
              timer: 2000,
              timerProgressBar: true,
            })
          break;
          default:
            Swal.fire({
              title: "Error fetching data",
              text: `${error.error.status.message}`,
              icon: "error",
              timer: 2000,
              timerProgressBar: true,
            })
        }
      })
    )
  }

  
  changeMetricSystem(metricSystem: string){
    this.metricSystem = metricSystem;
    localStorage.setItem('metricSystem', metricSystem);
    if(this.searchForm.value.city){
      this.searchByCity();
    } else{
      this.getUserLocation();
    }    
  }

  getMetricSymbol(metric: string): string {
    switch(metric){
      case '&units=metric':
        return 'C'
      case '&units=imperial':
          return 'F'
      case '&units=standard':
          return 'K'
      default:
          return ''
    }
  }

  setAsLocation(lat:number, lon:number){
    const userLocation = {lat:lat, lon:lon};
    this.userLocation = userLocation
    const userLocationToString = JSON.stringify(userLocation);
    localStorage.setItem('userLocation', userLocationToString);    
  }

  toggleAdvanced(){
    this.showAdvanced = !this.showAdvanced;
    const showAdvancedToString = JSON.stringify(this.showAdvanced);
    localStorage.setItem('showAdvanced', showAdvancedToString);  
  }

  getWindDirection(deg: number): string {
    switch (true) {
        case (deg >= 0 && deg < 22.5):
            return 'N';
        case (deg >= 22.5 && deg < 67.5):
            return 'NE';
        case (deg >= 67.5 && deg < 112.5):
            return 'E';
        case (deg >= 112.5 && deg < 157.5):
            return 'SE';
        case (deg >= 157.5 && deg < 202.5):
            return 'S';
        case (deg >= 202.5 && deg < 247.5):
            return 'SW';
        case (deg >= 247.5 && deg < 292.5):
            return 'W';
        case (deg >= 292.5 && deg < 337.5):
            return 'NW';
        default:
            return 'N';
    }
}




ngOnDestroy(): void {
  document.removeEventListener('click', this.onDocumentClick.bind(this));
  this.subscriptions.unsubscribe();
}


}