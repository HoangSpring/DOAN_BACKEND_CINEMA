import { useState, useMemo } from 'react';
import { useParams, Link } from 'react-router-dom';
import { useQuery } from '@tanstack/react-query';
import { format, addDays } from 'date-fns';
import { vi } from 'date-fns/locale';
import api from '../lib/api';
import { Badge } from '../components/ui/badge';
import { Button } from '../components/ui/button';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '../components/ui/tabs';
import { Skeleton } from '../components/ui/skeleton';
import { Clock, Calendar } from 'lucide-react';

interface Showtime {
  id: number;
  start_time: string;
  end_time: string;
  room: { id: number; name: string };
  format: string;
}

interface MovieDetail {
  id: number;
  title: string;
  description: string;
  duration_minutes: number;
  age_rating: string;
  poster_url: string;
  trailer_url: string;
  tags: { id: number; name: string }[];
}

export default function MovieDetailsPage() {
  const { id } = useParams<{ id: string }>();
  
  // Generate next 7 days for tabs
  const dateTabs = useMemo(() => {
    const today = new Date();
    return Array.from({ length: 7 }).map((_, i) => {
      const d = addDays(today, i);
      return {
        dateStr: format(d, 'yyyy-MM-dd'),
        displayDay: format(d, 'EEEE', { locale: vi }),
        displayDate: format(d, 'dd/MM'),
      };
    });
  }, []);

  const [selectedDate, setSelectedDate] = useState(dateTabs[0].dateStr);

  const { data: movie, isLoading: isMovieLoading } = useQuery({
    queryKey: ['movie', id],
    queryFn: async () => {
      const res = await api.get(`/movies/${id}`);
      return res.data.data as MovieDetail;
    },
  });

  const { data: showtimes, isLoading: isShowtimesLoading } = useQuery({
    queryKey: ['showtimes', id, selectedDate],
    queryFn: async () => {
      const res = await api.get(`/movies/${id}/showtimes?date=${selectedDate}`);
      return res.data.data as Showtime[];
    },
    enabled: !!id,
  });

  if (isMovieLoading) {
    return (
      <div className="container mx-auto px-4 py-8">
        <div className="flex flex-col md:flex-row gap-8">
          <Skeleton className="w-full md:w-1/3 aspect-[2/3] rounded-xl" />
          <div className="w-full md:w-2/3 space-y-4">
            <Skeleton className="h-12 w-3/4" />
            <Skeleton className="h-6 w-1/4" />
            <Skeleton className="h-32 w-full" />
            <Skeleton className="h-64 w-full mt-8" />
          </div>
        </div>
      </div>
    );
  }

  if (!movie) {
    return (
      <div className="container mx-auto px-4 py-24 text-center">
        <h2 className="text-2xl font-bold mb-4">Không tìm thấy phim</h2>
        <Button asChild><Link to="/">Quay về trang chủ</Link></Button>
      </div>
    );
  }

  return (
    <div>
      {/* Hero Section */}
      <div className="relative w-full overflow-hidden bg-black/90 pt-8 pb-12 md:pt-16 md:pb-24">
        {/* Blurred Background */}
        <div 
          className="absolute inset-0 opacity-20 blur-3xl scale-110"
          style={{ backgroundImage: `url(${movie.poster_url})`, backgroundSize: 'cover', backgroundPosition: 'center' }}
        />
        
        <div className="container relative z-10 mx-auto px-4">
          <div className="flex flex-col md:flex-row gap-8 md:gap-12 items-center md:items-start">
            <div className="w-2/3 sm:w-1/2 md:w-1/4 shrink-0">
              <img 
                src={movie.poster_url || 'https://via.placeholder.com/400x600'} 
                alt={movie.title}
                className="w-full rounded-2xl shadow-2xl border border-white/10"
              />
            </div>
            
            <div className="flex-1 text-center md:text-left text-white space-y-6">
              <h1 className="text-4xl md:text-5xl lg:text-6xl font-bold tracking-tight">{movie.title}</h1>
              
              <div className="flex flex-wrap items-center justify-center md:justify-start gap-4">
                <Badge variant="destructive" className="text-sm px-3 py-1">{movie.age_rating}</Badge>
                <div className="flex items-center gap-1.5 text-white/80">
                  <Clock className="w-4 h-4" />
                  <span>{movie.duration_minutes} phút</span>
                </div>
                {movie.tags.map(tag => (
                  <Badge key={tag.id} variant="outline" className="text-white/80 border-white/30 bg-white/5">
                    {tag.name}
                  </Badge>
                ))}
              </div>
              
              <div className="prose prose-invert max-w-none text-white/70 text-lg leading-relaxed">
                <p>{movie.description}</p>
              </div>
            </div>
          </div>
        </div>
      </div>

      {/* Showtimes Section */}
      <div className="container mx-auto px-4 py-12">
        <h2 className="text-3xl font-bold mb-8 flex items-center gap-2">
          <Calendar className="w-8 h-8 text-primary" />
          Lịch Chiếu
        </h2>

        <Tabs defaultValue={selectedDate} onValueChange={setSelectedDate} className="w-full">
          <TabsList className="w-full justify-start overflow-x-auto h-auto p-1 bg-muted/50 rounded-xl flex-nowrap scrollbar-hide">
            {dateTabs.map((tab) => (
              <TabsTrigger 
                key={tab.dateStr} 
                value={tab.dateStr}
                className="flex-col py-3 px-6 rounded-lg data-[state=active]:bg-primary data-[state=active]:text-primary-foreground min-w-[100px]"
              >
                <span className="text-xs uppercase font-medium opacity-80">{tab.displayDay}</span>
                <span className="text-lg font-bold">{tab.displayDate}</span>
              </TabsTrigger>
            ))}
          </TabsList>
          
          <TabsContent value={selectedDate} className="mt-8">
            {isShowtimesLoading ? (
              <div className="space-y-4">
                <Skeleton className="h-16 w-full" />
                <Skeleton className="h-16 w-full" />
              </div>
            ) : showtimes && showtimes.length > 0 ? (
              <div className="grid gap-4">
                {/* Group by room format (e.g. 2D, 3D) if available, but for now just list them */}
                <div className="bg-card border rounded-xl p-6">
                  <div className="flex flex-wrap gap-4">
                    {showtimes.map((st) => (
                      <Button 
                        key={st.id} 
                        variant="outline" 
                        size="lg" 
                        className="text-lg py-6 hover:border-primary hover:text-primary transition-colors flex flex-col gap-1 h-auto"
                        asChild
                      >
                        <Link to={`/showtimes/${st.id}/seats`}>
                          <span className="font-bold">{format(new Date(st.start_time), 'HH:mm')}</span>
                          <span className="text-xs font-normal text-muted-foreground">{st.room.name}</span>
                        </Link>
                      </Button>
                    ))}
                  </div>
                </div>
              </div>
            ) : (
              <div className="text-center py-16 bg-muted/20 rounded-xl border border-dashed">
                <p className="text-xl text-muted-foreground">Không có suất chiếu nào trong ngày này.</p>
                <p className="text-sm text-muted-foreground mt-2">Vui lòng chọn một ngày khác.</p>
              </div>
            )}
          </TabsContent>
        </Tabs>
      </div>
    </div>
  );
}
